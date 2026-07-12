<?php

namespace App\Http\Controllers\User;

use App\Enums\UserRole;
use App\Http\Controllers\AppBaseController;
use App\Mail\RegistrationSuccessMail;
use App\Models\Audit;
use Spatie\Permission\Models\Role;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Passport\Client as OClient;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Throwable;

#[OA\Info(
    version: "1.0.0",
    description: "Tài liệu API cho hệ thống An Thuận Clinic",
    title: "An Thuận Clinic API Documentation"
)]
#[OA\SecurityScheme(
    securityScheme: "BearerAuth",
    type: "http",
    scheme: "bearer"
)]
class AuthController extends AppBaseController
{
    public function index()
    {
        return view('auth.login');
    }

    public function registerPage()
    {
        return view('auth.register');
    }

    public function webRegister(Request $request)
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
            'phone_number' => $this->normalizeVietnamesePhone((string) $request->input('phone_number')),
        ]);

        $validator = Validator::make($request->all(), [
            'username' => ['bail', 'required', 'string', 'min:2', 'max:255', "regex:/^[\p{L}\s.'-]+$/u"],
            'phone_number' => ['bail', 'required', 'string', 'regex:/^0(3|5|7|8|9)[0-9]{8}$/'],
            'email' => ['bail', 'required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => ['bail', 'required', 'string', 'min:8', 'max:64'],
        ], [
            'username.required' => 'Vui lòng nhập họ và tên.',
            'username.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'username.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'username.regex' => 'Họ và tên không hợp lệ.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 64 ký tự.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->has('phone_number')) {
                return;
            }

            $phoneNumber = $this->normalizeVietnamesePhone((string) $request->input('phone_number'));

            if (User::where('phone_number', $phoneNumber)->exists()) {
                $validator->errors()->add('phone_number', 'Số điện thoại đã được sử dụng.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('password'));
        }

        $username = trim((string) $request->input('username'));
        $email = trim((string) $request->input('email'));
        $phoneNumber = $this->normalizeVietnamesePhone((string) $request->input('phone_number'));

        // Validation above only rejects emails still used by an active account, so any
        // record found here is guaranteed to be soft-deleted: restore it instead of
        // inserting a new row, since the DB-level unique index on `email` doesn't know
        // about soft deletes and would otherwise reject the insert.
        $existingUser = User::withTrashed()->where('email', $email)->first();

        try {
            if ($existingUser) {
                $existingUser->restore();
                $existingUser->forceFill([
                    'username' => $username,
                    'phone_number' => $phoneNumber,
                    'password' => Hash::make($request->input('password')),
                    'is_active' => true,
                ])->save();
                $user = $existingUser;
            } else {
                $user = User::create([
                    'username' => $username,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
                    'password' => Hash::make($request->input('password')),
                    'is_active' => true,
                ]);
            }
        } catch (QueryException $e) {
            // Safety net for a race condition: two concurrent submissions could both pass
            // the phone_number uniqueness check above (a plain SELECT, not locked) before
            // either INSERT runs. The DB-level unique index (added in the
            // add_unique_to_users_phone_number migration) is the real guard here.
            Log::error('Đăng ký thất bại do vi phạm ràng buộc dữ liệu duy nhất: ' . $e->getMessage());

            $field = str_contains($e->getMessage(), 'phone_number') ? 'phone_number' : 'email';
            $message = $field === 'phone_number' ? 'Số điện thoại đã được sử dụng.' : 'Email đã được sử dụng.';

            return back()->withErrors([$field => $message])->withInput($request->except('password'));
        }

        $user->syncRoles([UserRole::CUSTOMER->value]);

        try {
            Mail::to($user->email)->send(new RegistrationSuccessMail($user));
        } catch (Throwable $e) {
            Log::error('Gửi mail xác nhận đăng ký thất bại: ' . $e->getMessage());

            return redirect()
                ->route('auth.loginpage')
                ->with('warning', 'Đăng ký tài khoản thành công nhưng không thể gửi email xác nhận. Vui lòng đăng nhập, bạn có thể thử lại việc xác nhận sau.');
        }

        return redirect()
            ->route('auth.loginpage')
            ->with('success', 'Đăng ký tài khoản thành công! Email xác nhận đã được gửi tới ' . $user->email . '. Vui lòng đăng nhập.');
    }

    public function webLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => [
                'bail',
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $login = trim((string) $value);
                    $phoneCandidate = $this->normalizeVietnamesePhone($login);
                    $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
                    $isVietnamesePhone = preg_match('/^0(3|5|7|8|9)[0-9]{8}$/', $phoneCandidate) === 1;

                    if (! $isEmail && ! $isVietnamesePhone) {
                        $fail('Email/SĐT không hợp lệ.');
                    }
                },
            ],
            'password' => ['bail', 'required', 'string', 'min:8', 'max:64'],
        ], [
            'login.required' => 'Vui lòng nhập Email/SĐT của bạn.',
            'login.max' => 'Email/SĐT không được vượt quá 255 ký tự.',
            'password.required' => 'Vui lòng nhập mật khẩu của bạn.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá 64 ký tự.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('login'));
        }

        $login = trim((string) $request->input('login'));
        $loginEmail = mb_strtolower($login);
        $loginPhone = $this->normalizeVietnamesePhone($login);
        $user = User::where('email', $loginEmail)
            ->orWhere('phone_number', $loginPhone)
            ->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['auth' => 'Hmmm. Email/SĐT hoặc mật khẩu không chính xác. Vui lòng thử lại.']);
        }

        if (! $user->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['auth' => 'Tài khoản của bạn đã ngưng hoạt động.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirectTo = $user->can('access-admin') ? route('admin.dashboard') : url('/');

        return redirect()
            ->intended($redirectTo)
            ->with('success', 'Đăng nhập thành công.');
    }

    public function webLogout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('auth.loginpage')
            ->with('success', 'Đăng xuất thành công.');
    }

    public function redirectToGoogle()
    {
        if (empty(config('services.google.client_id')) || empty(config('services.google.client_secret'))) {
            return redirect()
                ->route('auth.loginpage')
                ->with('error', 'Google OAuth chưa được cấu hình. Vui lòng điền GOOGLE_CLIENT_ID và GOOGLE_CLIENT_SECRET trong file .env.');
        }

        return $this->googleProvider()
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = $this->googleProvider()->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('auth.loginpage')
                ->with('error', config('app.debug')
                    ? 'Không thể đăng nhập bằng Google: '.$e->getMessage()
                    : 'Không thể đăng nhập bằng Google. Vui lòng thử lại.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()
                ->route('auth.loginpage')
                ->with('error', 'Tài khoản Google chưa cung cấp email.');
        }

        $email = mb_strtolower(trim($email));

        $rawGoogleUser = $googleUser->user ?? [];

        if (array_key_exists('email_verified', $rawGoogleUser) && ! filter_var($rawGoogleUser['email_verified'], FILTER_VALIDATE_BOOLEAN)) {
            return redirect()
                ->route('auth.loginpage')
                ->with('error', 'Email Google chưa được xác thực.');
        }

        $user = User::withTrashed()->where('email', $email)->first();

        if ($user?->trashed()) {
            $user->restore();
        }

        if (! $user) {
            $user = User::create([
                'username' => $this->makeUniqueUsername($email, $googleUser->getName()),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([UserRole::CUSTOMER->value]);
        } elseif (! $user->is_active) {
            return redirect()
                ->route('auth.loginpage')
                ->with('error', 'Tài khoản của bạn đã ngưng hoạt động.');
        } else {
            $user->forceFill([
                'google_id' => $googleUser->getId() ?: $user->google_id,
                'avatar_url' => $googleUser->getAvatar() ?: $user->avatar_url,
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()
            ->intended($user->isAdmin() ? route('admin.dashboard') : url('/'))
            ->with('success', 'Đăng nhập bằng Google thành công.');
    }

    private function makeUniqueUsername(string $email, ?string $fullName = null): string
    {
        $base = trim((string) ($fullName ?: Str::before($email, '@'))) ?: 'User';
        $base = mb_substr($base, 0, 240);

        $username = $base;
        $counter = 2;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $suffix = ' '.$counter++;
            $username = mb_substr($base, 0, 255 - mb_strlen($suffix)).$suffix;
        }

        return $username;
    }

    private function normalizeVietnamesePhone(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/[\s.\-]+/', '', trim($phoneNumber));

        if (str_starts_with($phoneNumber, '+84')) {
            return '0'.substr($phoneNumber, 3);
        }

        return $phoneNumber;
    }

    private function googleProvider()
    {
        $provider = Socialite::driver('google')->stateless();
        $caBundle = storage_path('certs/cacert.pem');

        if (is_file($caBundle)) {
            $provider->setHttpClient(new Client([
                'verify' => $caBundle,
            ]));
        }

        return $provider;
    }

    #[OA\Post(
        path: "/api/auth/login",
        summary: "Đăng nhập",
        description: "Đăng nhập bằng username và password để nhận access token",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "password"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "admin"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Admin@123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Đăng nhập thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Đăng nhập thành công!"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "username", type: "string", example: "admin"),
                                new OA\Property(property: "role", type: "string", example: "admin"),
                                new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 86400),
                                new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
                                new OA\Property(property: "refresh_token", type: "string", example: "def50200...")
                            ],
                            type: "object"
                        )
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "401",
                description: "Sai thông tin đăng nhập hoặc OAuth client không hợp lệ",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Sai mật khẩu!")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "422",
                description: "Lỗi xác thực dữ liệu hoặc user không tồn tại",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Vui lòng nhập username!")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "500",
                description: "Lỗi cấu hình OAuth hoặc lỗi tạo token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Client secret chưa được cấu hình")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => "Vui lòng nhập username!",
            'password.required' => "Vui lòng nhập mật khẩu",
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), 422);
        }

        $credentials = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        $user = User::where('username', $request->input('username'))->first();

        if (! $user) {
            return $this->sendError('User không tồn tại trong hệ thống!', 422);
        }

        if (! $user->role) {
            return $this->sendError('Tài khoản không có quyền truy cập.', 422);
        }

        if (! $user->is_active) {
            return $this->sendError('Tài khoản của bạn đã ngưng hoạt động.', 403);
        }

        if (! Auth::attempt($credentials)) {
            Audit::create([
                'user_type' => 'App\Models\User',
                'user_id' => $user->id,
                'event' => 'login fail',
                'auditable_type' => 'App\Models\User',
                'auditable_id' => $user->id,
                'url' => url('/login'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->server('HTTP_USER_AGENT'),
            ]);

            return $this->sendError('Sai mật khẩu!', 401);
        }

        \DB::table('oauth_access_tokens')
            ->where('user_id', $user->id)
            ->update(['revoked' => true]);

        $oclient = OClient::where(function ($q) {
            $q->where('provider', 'users')
                ->orWhere('name', 'like', '%Password%');
        })
            ->where('revoked', false)
            ->whereJsonContains('grant_types', 'password')
            ->first();

        if (! $oclient) {
            return $this->sendError('Không tìm thấy Client', 401);
        }

        $clientSecret = config('passport.password_client_secret');

        if (! $clientSecret) {
            return $this->sendError('Client secret chưa được cấu hình', 500);
        }

        $tokenData = [
            'username' => $user->username,
            'password' => $request->password,
            'grant_type' => 'password',
            'client_id' => $oclient->id,
            'client_secret' => $clientSecret,
            'scope' => '*'
        ];

        $request->request->add($tokenData);

        //login success
        Audit::create([
            'user_type' => 'App\Models\User',
            'user_id' => Auth::user()->id,
            'event' => 'login success',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => Auth::user()->id,
            'url' => url('/login'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->server('HTTP_USER_AGENT')
        ]);

        $tokenRequest = Request::create('/oauth/token', 'post', $request->all());
        $tokenResponse = app()->handle($tokenRequest);
        $tokenResult = json_decode($tokenResponse->getContent());

        if (! $tokenResponse->isSuccessful()) {
            $errorContent = json_decode($tokenResponse->getContent());
            $errorMessage = $errorContent->message ?? $errorContent->error_description ?? 'Lỗi xác thực OAuth token';
            return $this->sendError($errorMessage, $tokenResponse->getStatusCode());
        }

        if (! $tokenResult || ! isset($tokenResult->token_type)) {
            return $this->sendError('Lỗi tạo token xác thực', 500);
        }


        $user->token_type = $tokenResult->token_type;
        $user->expires_in = $tokenResult->expires_in;
        $user->access_token = $tokenResult->access_token;
        $user->refresh_token = $tokenResult->refresh_token;

        return $this->sendResponse($user, "Đăng nhập thành công!");
    }

    #[OA\Get(
        path: "/api/auth/logout",
        summary: "Đăng xuất",
        description: "Hủy token hiện tại và đăng xuất khỏi hệ thống",
        tags: ["Auth"],
        security: [["BearerAuth" => []]],
        responses: [
            new OA\Response(
                response: "200",
                description: "Đăng xuất thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Đăng xuất thành công!"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "401",
                description: "Chưa xác thực",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Vui lòng đăng nhập để thực hiện thao tác này.")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "404",
                description: "Không tìm thấy token đang hoạt động",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Không tìm thấy token đang hoạt động")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function logout(Request $request)
    {
        $user = $request->user();

        $accessToken = $user->token();

        if (! $accessToken) {
            return $this->sendError('Không tìm thấy token đang hoạt động', 404);
        }

        $accessToken->revoke();

        \DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        return $this->sendResponse([], 'Đăng xuất thành công!');
    }

    #[OA\Post(
        path: "/api/auth/refresh-token",
        summary: "Làm mới Access Token",
        description: "Sử dụng refresh token để lấy lại access token mới",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["refresh_token"],
                properties: [
                    new OA\Property(property: "refresh_token", type: "string", example: "def50200...")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Làm mới token thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Refresh token thành công!"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 86400),
                                new OA\Property(property: "access_token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
                                new OA\Property(property: "refresh_token", type: "string", example: "def50200...")
                            ],
                            type: "object"
                        )
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "401",
                description: "Client không hợp lệ",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Không tìm thấy Client")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "422",
                description: "Lỗi thiếu thông tin",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Vui lòng cung cấp refresh token.")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "500",
                description: "Lỗi máy chủ hoặc cấu hình OAuth",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Client secret chưa được cấu hình")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), 422);
        }

        $oclient = OClient::where(function ($q) {
            $q->where('provider', 'users')
                ->orWhere('name', 'like', '%Password%');
        })
            ->where('revoked', false)
            ->whereJsonContains('grant_types', 'password')
            ->first();

        if (! $oclient) {
            return $this->sendError('Không tìm thấy Client', 401);
        }

        $clientSecret = config('passport.password_client_secret');

        if (! $clientSecret) {
            return $this->sendError('Client secret chưa được cấu hình', 500);
        }

        $tokenData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $oclient->id,
            'client_secret' => $clientSecret,
            'scope' => '*'
        ];

        $tokenRequest = Request::create('/oauth/token', 'post', $tokenData);
        $tokenResponse = app()->handle($tokenRequest);

        if (! $tokenResponse->isSuccessful()) {
            $errorContent = json_decode($tokenResponse->getContent());
            $errorMessage = $errorContent->message ?? $errorContent->error_description ?? 'Lỗi refresh token';
            return $this->sendError($errorMessage, $tokenResponse->getStatusCode());
        }

        $tokenResult = json_decode($tokenResponse->getContent());

        if (! $tokenResult || ! isset($tokenResult->access_token)) {
            return $this->sendError('Lỗi tạo token mới', 500);
        }

        return $this->sendResponse($tokenResult, 'Refresh token thành công!');
    }

    #[OA\Post(
        path: "/api/auth/request-forgot-password",
        summary: "Yêu cầu đổi mật khẩu",
        description: "Đặt lại mật khẩu mới thông qua username của người dùng",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "new_password"],
                properties: [
                    new OA\Property(property: "username", type: "string", example: "admin"),
                    new OA\Property(property: "new_password", type: "string", format: "password", example: "newpassword123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: "200",
                description: "Đổi mật khẩu thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Đổi mật khẩu thành công!")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "422",
                description: "User không tồn tại hoặc lỗi dữ liệu",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "User này không tồn tại trong hệ thống.")
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: "500",
                description: "Lỗi hệ thống",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Server error")
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function requestForgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'username' => 'required|string|exists:users,username',
                'new_password' => 'required|min:6'
            ], [
                'username.exists' => 'User này không tồn tại trong hệ thống.'
            ]);

            if ($validator->fails()) {
                return response()->json(["success" => false, "message" => $validator->errors()->first()], 422);
            }

            $targetUser = User::where('username', $request->username)->first();

            User::where('id', $targetUser->id)->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                "success" => true,
                "message" => "Đổi mật khẩu thành công!"
            ]);

        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => $e->getMessage()], 500);
        }
    }
}
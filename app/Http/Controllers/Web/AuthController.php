<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\AppBaseController;
use App\Models\Audit;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|min:2|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'email'        => 'required|email|max:255|unique:users,email',
            'password'     => 'required|string|min:6|confirmed',
        ], [
            'display_name.required' => 'Vui lòng nhập họ và tên.',
            'display_name.min'      => 'Họ và tên phải có ít nhất 2 ký tự.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.unique'   => 'Số điện thoại đã được sử dụng.',
            'email.required'        => 'Vui lòng nhập email.',
            'email.email'           => 'Email không hợp lệ.',
            'email.unique'          => 'Email đã được sử dụng.',
            'password.required'     => 'Vui lòng nhập mật khẩu.',
            'password.min'          => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed'    => 'Mật khẩu xác nhận không khớp.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->except('password', 'password_confirmation'));
        }

        $customerRole = Role::firstOrCreate(['name' => UserRole::CUSTOMER->value]);

        $user = User::create([
            'username'     => $this->makeUniqueUsername($request->email, $request->display_name),
            'display_name' => $request->display_name,
            'phone_number' => $request->phone_number,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role_id'      => $customerRole->id,
            'is_active'    => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(url('/'))->with('success', 'Đăng ký tài khoản thành công!');
    }

    public function webLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('login'));
        }

        $login = trim((string) $request->input('login'));
        $user = User::where('email', $login)
            ->orWhere('phone_number', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Email/SĐT hoặc mật khẩu không chính xác! Vui lòng thử lại.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()
            ->intended($user->isAdmin() ? route('admin.dashboard') : url('/'))
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
            $customerRole = Role::firstOrCreate(['name' => UserRole::CUSTOMER->value]);

            $user = User::create([
                'username' => $this->makeUniqueUsername($email, $googleUser->getName()),
                'display_name' => $googleUser->getName(),
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'role_id' => $customerRole->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $googleUser->getId() ?: $user->google_id,
                'display_name' => $googleUser->getName() ?: $user->display_name,
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

    private function makeUniqueUsername(string $email, ?string $displayName = null): string
    {
        $baseSource = $displayName ?: Str::before($email, '@');
        $base = Str::lower(Str::ascii($baseSource));
        $base = preg_replace('/[^a-z0-9_]+/', '_', $base) ?: 'user';
        $base = trim($base, '_') ?: 'user';
        $base = substr($base, 0, 40);

        $username = $base;
        $counter = 1;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $suffix = '_'.$counter++;
            $username = substr($base, 0, 50 - strlen($suffix)).$suffix;
        }

        return $username;
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


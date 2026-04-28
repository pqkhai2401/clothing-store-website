<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\User;
use App\Models\Audit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

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
    #[OA\Post(
        path: "/api/auth/login",
        summary: "Đăng nhập",
        description: "Đăng nhập bằng user_name và password để nhận access token",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_name", "password"],
                properties: [
                    new OA\Property(property: "user_name", type: "string", example: "admin123"),
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
                                new OA\Property(property: "name", type: "string", example: "Admin"),
                                new OA\Property(property: "user_name", type: "string", example: "admin123"),
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
                        new OA\Property(property: "message", type: "string", example: "Vui lòng nhập user_name!")
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
            'user_name' => 'required',
            'password' => 'required',
        ], [
            'user_name.required' => "Vui lòng nhập user_name!",
            'password.required' => "Vui lòng nhập mật khẩu",
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), 422);
        }

        $credentials = [
            'user_name' => $request->input('user_name'),
            'password' => $request->input('password'),
        ];

        $user = User::where('user_name', $request->input('user_name'))->first();

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
            'username' => $user->user_name,
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
        description: "Đặt lại mật khẩu mới thông qua user_name của người dùng",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_name", "new_password"],
                properties: [
                    new OA\Property(property: "user_name", type: "string", example: "admin123"),
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
                'user_name' => 'required|user_name|exists:users,user_name',
                'new_password' => 'required|min:6'
            ], [
                'user_name.exists' => 'User này không tồn tại trong hệ thống.'
            ]);

            if ($validator->fails()) {
                return response()->json(["success" => false, "message" => $validator->errors()->first()], 422);
            }

            $targetUser = User::where('user_name', $request->user_name)->first();

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

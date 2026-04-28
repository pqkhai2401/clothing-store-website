<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Passport\Client as OClient;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Audit;
use App\Repositories\UserRepository;

class AuthController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        return view('auth.login');
    }

    public function login(AuthRequest $request)
    {
        $request->validated();

        $credentials = [
            'user_name' => $request->input('user_name'),
            'password' => $request->input('password'),
        ];

        $user = User::where('user_name', $request->input('user_name'))->first();

        if (! $user) {
            return back()->with('error', 'Username không tồn tại trong hệ thống!');
        }

        if ($user->role != 'admin') {
            return back()->with('error', 'Tài khoản không có quyền truy cập.');
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

            return back()->with('error', 'Sai mật khẩu!');
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
            return back()->with('error', 'Không tìm thấy Client');
        }

        $clientSecret = config('passport.password_client_secret');

        if (! $clientSecret) {
            return back()->with('error', 'Client secret chưa được cấu hình');
        }

        $tokenData = [
            'username' => $user->email,
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
            return back()->with('error', $errorMessage);
        }

        if (! $tokenResult || ! isset($tokenResult->token_type)) {
            return back()->with('error', 'Lỗi tạo token xác thực');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('auth.loginpage')->with('success', 'Đăng xuất thành công');
    }
}

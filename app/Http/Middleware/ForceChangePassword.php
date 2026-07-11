<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chặn mọi thao tác trong khu vực admin (trừ trang hồ sơ cá nhân và đăng xuất)
 * cho tới khi tài khoản vừa được admin reset mật khẩu tự đổi mật khẩu mới.
 */
class ForceChangePassword
{
    private const ALLOWED_ROUTES = [
        'admin.dashboard',
        'admin.profile',
        'admin.profile.update',
        'auth.logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (bool) $user->must_change_password
            && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTES, true)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Bạn phải đổi mật khẩu trước khi tiếp tục sử dụng hệ thống.',
                    'must_change_password' => true,
                ], 423);
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('warning', 'Bạn phải đổi mật khẩu trước khi tiếp tục sử dụng hệ thống.');
        }

        return $next($request);
    }
}

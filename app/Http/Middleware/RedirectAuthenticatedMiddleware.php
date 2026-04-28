<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectAuthenticatedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            switch (Auth::user()->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công!');
                default:
                    return redirect()->route('404-not-found');
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! (bool) $user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã ngừng hoạt động.',
                ], 403);
            }

            return redirect()
                ->route('auth.loginpage')
                ->withErrors(['auth' => 'Tài khoản của bạn đã ngừng hoạt động.']);
        }

        return $next($request);
    }
}

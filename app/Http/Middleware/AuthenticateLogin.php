<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticateLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to login page and password reset routes
        $allowedRoutes = [
            'login',
            'forgot-password',
            'verify-code',
            'reset-password',
            'password/*'
        ];

        foreach ($allowedRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Check authentication
        if (! Auth::check()) {
            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Please login to continue"
                ], 401);
            }

            // Redirect to login for regular requests
            return redirect()->route('auth.loginpage')->with([
                'error' => "Please login to continue"
            ]);
        }

        return $next($request);
    }
}

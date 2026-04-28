<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToLoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Bỏ qua tất cả API requests
        if ($request->is('api/*')) {
            return $next($request);
        }

        if ($request->path() === "/")
            return redirect()->route('auth.loginpage');

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CustomPassportResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Thêm check route refresh token
        if ($request->is('api/auth/refresh-token')) {
            return $response;
        }
        // Nếu là unauthorized response từ Passport
        if ($response->getStatusCode() === 401) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn',
                'data' => null
            ], 401);
        }

        return $response;
    }
}

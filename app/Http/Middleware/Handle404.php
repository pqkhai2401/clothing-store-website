<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Handle404
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Closure(Request): (Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$response = $next($request);

		// If the response is a 404, redirect to our custom 404 page
		if ($response->getStatusCode() === 404) {
			return redirect()->route('404-not-found');
		}

		return $response;
	}
}

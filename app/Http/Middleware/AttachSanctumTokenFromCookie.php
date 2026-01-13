<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttachSanctumTokenFromCookie
{
    /**
     * The name of the cookie that contains the authentication token.
     */
    protected const TOKEN_COOKIE_NAME = 'auth_token';

    /**
     * Handle an incoming request.
     *
     * This middleware checks if an authentication token is present in the 'auth_token' cookie,
     * and if there's no Authorization header already present in the request, it adds the token
     * as a Bearer token in the Authorization header.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasCookie(self::TOKEN_COOKIE_NAME)) {
            $token = $request->cookie(self::TOKEN_COOKIE_NAME);

            // Decode the token in case it contains URL-encoded characters (like %7C for |)
            $decodedToken = urldecode($token);

            if (!empty(trim($decodedToken)) && $this->isValidTokenFormat($decodedToken) && !$request->headers->has('Authorization')) {
                $request->headers->set('Authorization', "Bearer {$decodedToken}");
            }
        }

        return $next($request);
    }

    /**
     * Validates that the token has a reasonable format for a Sanctum token.
     * This helps prevent injection of invalid tokens.
     *
     * @param  string  $token
     * @return bool
     */
    protected function isValidTokenFormat(string $token): bool
    {
        $sanctumPattern = '/^[a-zA-Z0-9\-\_\.]+\|[a-zA-Z0-9\-\_\.]+$/'; // Pattern for Sanctum tokens (name|value)

        // Check for reasonable length (between 10 and 5000 characters)
        if (strlen($token) < 10 || strlen($token) > 5000) {
            return false;
        }

        // Validate against known patterns
        return preg_match($sanctumPattern, $token);
    }
}

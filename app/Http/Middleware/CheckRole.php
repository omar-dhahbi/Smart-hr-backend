<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return $this->unauthorized('Your token has expired. Please login again.');
        } catch (TokenInvalidException $e) {
            return $this->unauthorized('Your token is invalid. Please login again.');
        } catch (JWTException $e) {
            return $this->unauthorized('Please attach a Bearer Token to your request.');
        }

        if ($user && in_array($user->role, $roles)) {
            return $next($request);
        }

        return $this->unauthorized();
    }

    private function unauthorized($message = 'You are unauthorized to access this resource')
    {
        return response()->json([
            'message' => $message,
            'success' => false
        ], 401);
    }

}

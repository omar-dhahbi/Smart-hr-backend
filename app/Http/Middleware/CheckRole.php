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
        return $this->unauthorized('Token expired.');
    } catch (TokenInvalidException $e) {
        return $this->unauthorized('Token invalid.');
    } catch (JWTException $e) {
        return $this->unauthorized('Token required.');
    }

    if (!$user) {
        return $this->unauthorized();
    }

    foreach ($roles as $role) {

        if ($user->role === $role) {
            return $next($request);
        }

        if ($role === 'employee:chef' &&
            $user->role === 'employee' &&
            $user->grade === 'chef') {
            return $next($request);
        }
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

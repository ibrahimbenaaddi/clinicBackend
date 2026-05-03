<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Laravel\Sanctum\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isPatient extends Guard
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->__invoke($request);

        if (!$user) {
            return self::unAuth();
        }

        if ($user && $user->role === 'patient' && $user->tokenCan('patient')) {
            Auth::setUser($user);
            return $next($request);
        }

        return self::unAuth();
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Doctor;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Guard;
use Symfony\Component\HttpFoundation\Response;

class isDoctor extends Guard
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
        
        $role = $user instanceof Doctor ? $user?->user?->role : $user?->role;
        if ($role === 'doctor' && $user->tokenCan('doctor')) {
            Auth::setUser($user);
            return $next($request);
        }

        return self::unAuth();
    }
}

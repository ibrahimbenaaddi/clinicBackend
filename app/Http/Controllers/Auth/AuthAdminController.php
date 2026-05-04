<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthAdminService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class AuthAdminController extends Controller
{
    use ApiResponse;
    
    private AuthAdminService $service;

    public function __construct(){
        $this->service = new AuthAdminService();
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $doctor = $this->service->login($credentials)) {
                return self::failled('login', 'AuthAdminController', 'login');
            }
            $token = $doctor->createToken('adminToken', ['admin'], now()->addMinutes(5))->plainTextToken;
            return self::authSuccess(new UserResource($doctor), $token, 'login');
        } catch (Exception $e) {
            return self::failled('login', 'AuthAdminController', 'login', $e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return self::logoutSuccess();
        } catch (Exception $e) {
            return self::failled('logout', 'AuthAdminController', 'logout', $e);
        }
    }
}

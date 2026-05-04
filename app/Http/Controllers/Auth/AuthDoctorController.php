<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\UserResource;
use App\Services\AuthDoctorService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class AuthDoctorController extends Controller
{

    use ApiResponse;

    private AuthDoctorService $service;

    public function __construct()
    {
        $this->service = new AuthDoctorService();
    }

    public function register(StoreDoctorRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $doctor = $this->service->register($credentials)) {
                return self::failled('register', 'AuthDoctorController', 'register');
            }

            $token = $doctor->createToken('doctorToken', ['doctor'], now()->addMinutes(5))->plainTextToken;
            return self::authSuccess(new DoctorResource($doctor), $token, 'register');
        } catch (Exception $e) {
            return self::failled('register', 'AuthDoctorController', 'register', $e);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $doctor = $this->service->login($credentials)) {
                return self::failled('login', 'AuthDoctorController', 'login');
            }
            $token = $doctor->createToken('doctorToken', ['doctor'], now()->addMinutes(5))->plainTextToken;
            return self::authSuccess(new UserResource($doctor), $token, 'login');
        } catch (Exception $e) {
            return self::failled('login', 'AuthDoctorController', 'login', $e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return self::logoutSuccess();
        } catch (Exception $e) {
            return self::failled('logout', 'AuthDoctorController', 'logout', $e);
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\StorePatientRequest;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Services\AuthPatientService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class AuthPatientController extends Controller
{

    use ApiResponse;

    private AuthPatientService $service;

    public function __construct()
    {
        $this->service = new AuthPatientService();
    }

    public function register(StorePatientRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $patient = $this->service->register($credentials)) {
                return self::failled('register', 'AuthPatientController', 'register');
            }

            $token = $patient->createToken('patientToken', ['patient'], now()->addMinutes(5))->plainTextToken;
            return self::authSuccess(new PatientResource($patient), $token, 'register');
        } catch (Exception $e) {
            return self::failled('register', 'AuthPatientController', 'register', $e);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            if (! $patient = $this->service->login($credentials)) {
                return self::failled('login', 'AuthPatientController', 'login');
            }
            $token = $patient->createToken('patientToken', ['patient'], now()->addMinutes(5))->plainTextToken;
            return self::authSuccess(new UserResource($patient), $token, 'login');
        } catch (Exception $e) {
            return self::failled('login', 'AuthPatientController', 'login', $e);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return self::logoutSuccess();
        } catch (Exception $e) {
            return self::failled('logout', 'AuthPatientController', 'logout', $e);
        }
    }
}

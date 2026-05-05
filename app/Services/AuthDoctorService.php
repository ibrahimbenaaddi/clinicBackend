<?php

namespace App\Services;

use Exception;
use App\Traits\ServiceResponse;
use Illuminate\Support\Facades\Auth;

class AuthDoctorService
{
    use ServiceResponse;

    private DoctorService $service;

    public function __construct()
    {
        $this->service = new DoctorService();
    }

    public function register(array $credentials)
    {
        try {
            if (! $doctor = $this->service->createDoctor($credentials)) {
                return self::theLog('register', 'AuthDoctorService');
            };
            return $doctor;
        } catch (Exception $e) {
            return self::theLog('register', 'AuthDoctorService', $e);
        }
    }

    public function login(array $credentials)
    {
        try {
            if (! Auth::attempt($credentials)) {
                return self::theLog('login', 'AuthDoctorService');
            }

            $doctor = Auth::user();
            if (blank($doctor)) {
                return self::theLog('login', 'AuthDoctorService');
            }
            if ($doctor->role !== 'doctor') {
                return self::theLog('login', 'AuthDoctorService');
            }
            return $doctor->load('doctor');
        } catch (Exception $e) {
            return self::theLog('login', 'AuthDoctorService', $e);
        }
    }
}
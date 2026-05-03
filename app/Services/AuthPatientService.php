<?php

namespace App\Services;

use Exception;
use App\Traits\ServiceResponse;
use Illuminate\Support\Facades\Auth;

class AuthPatientService
{
    use ServiceResponse;

    private PatientService $service;

    public function __construct()
    {
        $this->service = new PatientService();
    }

    public function register(array $credentials)
    {
        try {
            if (! $patient = $this->service->createPatient($credentials)) {
                return self::theLog('register', 'AuthPatientService');
            };
            return $patient;
        } catch (Exception $e) {
            return self::theLog('register', 'AuthPatientService', $e);
        }
    }

    public function login(array $credentials)
    {
        try {
            if (! Auth::attempt($credentials)) {
                return self::theLog('login', 'AuthPatientService');
            }

            $patient = Auth::user();
            if (blank($patient)) {
                return self::theLog('login', 'AuthPatientService');
            }

            return $patient->load('patient');
        } catch (Exception $e) {
            return self::theLog('login', 'AuthPatientService', $e);
        }
    }
}

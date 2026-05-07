<?php

namespace App\Services;

use App\Models\User;
use Exception;
use App\Traits\ServiceResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthPatientService
{
    use ServiceResponse;

    public function register(array $credentials)
    {
        try {
            DB::beginTransaction();

            $credentials['password'] = Hash::make($credentials['password']);

            $userData = Arr::only($credentials, ['firstname', 'lastname', 'email', 'password']);
            $userData['role'] = 'patient';
            $patientData = Arr::only($credentials, ['date_birth', 'address', 'phone', 'insurance_info']);

            $user = User::create($userData);
            if (blank($user)) {
                DB::rollBack();
                return self::theLog('register', 'AuthPatientService', new Exception("The patient profile is not created"));
            }

            $patient = $user->patient()->create($patientData);
            if (blank($patient)) {
                DB::rollBack();
                return self::theLog('register', 'AuthPatientService', new Exception("The patient profile is not created"));
            }

            $user->load('patient');
            DB::commit();
            return $user;
        } catch (Exception $e) {
            return self::theLog('register', 'AuthPatientService', $e);
        }
    }

    public function login(array $credentials)
    {
        try {
            if (! Auth::attempt($credentials)) {
                return self::theLog('login', 'AuthPatientService', new Exception("Invalid credentials provided."));
            }

            $patient = Auth::user();
            if (blank($patient)) {
                return self::theLog('login', 'AuthPatientService', new Exception("The user was not found"));
            }
            if ($patient->role !== 'patient') {
                return self::theLog('login', 'AuthPatientService', new Exception("The user has an invalid role"));
            }
            return $patient->load('patient');
        } catch (Exception $e) {
            return self::theLog('login', 'AuthPatientService', $e);
        }
    }
}

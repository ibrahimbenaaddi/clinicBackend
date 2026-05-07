<?php

namespace App\Services;

use App\Models\User;
use Exception;
use App\Traits\ServiceResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthDoctorService
{
    use ServiceResponse;

    public function register(array $credentials)
    {
        try {

            DB::beginTransaction();

            $credentials['password'] = Hash::make($credentials['password']);

            $userData = Arr::only($credentials, ['firstname', 'lastname', 'email', 'password']);
            $userData['role'] = 'doctor';
            $doctorData = Arr::only($credentials, ['specialization', 'license_number', 'phone']);

            $user = User::create($userData);
            if (blank($user)) {
                DB::rollBack();
                return self::theLog('register', 'AuthDoctorService', new Exception("The doctor profile is not created"));
            }

            $doctor = $user->doctor()->create($doctorData);
            if (blank($doctor)) {
                DB::rollBack();
                return self::theLog('register', 'AuthDoctorService', new Exception("The doctor profile is not created"));
            }

            $user->load('doctor');
            DB::commit();
            return $user;
        } catch (Exception $e) {
            return self::theLog('register', 'AuthDoctorService', $e);
        }
    }

    public function login(array $credentials)
    {
        try {
            if (! Auth::attempt($credentials)) {
                return self::theLog('login', 'AuthDoctorService', new Exception("Invalid credentials provided."));
            }

            $doctor = Auth::user();
            if (blank($doctor)) {
                return self::theLog('login', 'AuthDoctorService', new Exception("The user was not found"));
            }
            if ($doctor->role !== 'doctor') {
                return self::theLog('login', 'AuthDoctorService', new Exception("The user has an invalid role"));
            }
            return $doctor->load('doctor');
        } catch (Exception $e) {
            return self::theLog('login', 'AuthDoctorService', $e);
        }
    }
}

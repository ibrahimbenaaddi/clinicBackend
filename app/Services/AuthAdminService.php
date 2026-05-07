<?php

namespace App\Services;

use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthAdminService
{
    use ServiceResponse;
    
    public function login(array $credentials)
    {
        try {
            if (! Auth::attempt($credentials)) {
                return self::theLog('login', 'AuthAdminService', new Exception("Invalid credentials provided."));
            }

            $admin = Auth::user();
            if (blank($admin)) {
                return self::theLog('login', 'AuthAdminService', new Exception("The user was not found"));
            }
            if ($admin->role !== 'admin') {
                return self::theLog('login', 'AuthAdminService', new Exception("The user has an invalid role"));
            }
            return $admin;
        } catch (Exception $e) {
            return self::theLog('login', 'AuthAdminService', $e);
        }
    }
}
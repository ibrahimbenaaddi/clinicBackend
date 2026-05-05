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
                return self::theLog('login', 'AuthAdminService');
            }

            $admin = Auth::user();
            if (blank($admin)) {
                return self::theLog('login', 'AuthAdminService');
            }
            if ($admin->role !== 'admin') {
                return self::theLog('login', 'AuthAdminService');
            }
            return $admin;
        } catch (Exception $e) {
            return self::theLog('login', 'AuthAdminService', $e);
        }
    }
}
<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;

trait ServiceResponse
{

    private static function theLog(string $functionName, string $serviceName, ?Exception $error = null)
    {
        Log::error('error in ' . $functionName . '@' . $serviceName);
        if(! is_null($error)){
            Log::error('error is : ' . $error->getMessage());
        }
        return false;
    }
}

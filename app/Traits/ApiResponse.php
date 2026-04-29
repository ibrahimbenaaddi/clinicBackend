<?php

namespace App\Traits;

use App\Constants\ApiMessages;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{

    public static function readSuccess(JsonResource $resource)
    {
        return response()->json([
            'status' => true,
            'message' => ApiMessages::successMessages['read'],
            'data' => $resource
        ], 200);
    }

    public function createSuccess(JsonResource $resource)
    {
        return response()->json([
            'status' => true,
            'message' => ApiMessages::successMessages['create'],
            'data' => $resource
        ], 200);
    }

    public function updateSuccess(JsonResource $resource)
    {
        return response()->json([
            'status' => true,
            'message' => ApiMessages::successMessages['update'],
            'data' => $resource
        ], 200);
    }

    public function deleteSuccess()
    {
        return response()->json([
            'status' => true,
            'message' => ApiMessages::successMessages['delete']
        ], 200);
    }
    
    public static function failled(string $functionName, string $controllerName, string $action, ?Exception $error = null)
    {
        Log::error('error in ' . $functionName . '@' . $controllerName);
        if (! is_null($error)) {
            Log::error('error is : ' . $error->getMessage());
        }
        return response()->json([
            'status' => false,
            'message' => ApiMessages::failledMessages[$action],
        ], 404);
    }
}

<?php

namespace App\Traits;

use App\Constants\ApiMessages;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{

    public static function readSuccess(JsonResource $resource)
    {
        $response = [
            'status' => true,
            'message' => ApiMessages::successMessages['read'],
            'data' => $resource
        ];

        if ($resource->resource instanceof Paginator) {
            $response['pagination'] = [
                'current_page' => $resource->resource->currentPage(),
                'per_page' => $resource->resource->perPage(),
                'total' => $resource->resource->total(),
                'last_page' => $resource->resource->lastPage(),
            ];
        }
        return response()->json($response, 200);
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

    public function authSuccess(JsonResource $user, string $token, string $action)
    {
        return response()->json([
            'status' => true,
            'message' => ApiMessages::successMessages[$action],
            'data' => [
                'user ' => $user,
                'token' => $token
            ]
        ], 200);
    }

    public function logoutSuccess()
    {
        return response()->json([
            'status' => true,
            'message' => ApiMessages::successMessages['logout']
        ], 200);
    }

    public function unAuth()
    {
        return response()->json([
            'status' => false,
            'message' => ApiMessages::failledMessages['auth']
        ], 401);
    }
}

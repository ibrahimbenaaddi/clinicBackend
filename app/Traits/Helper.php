<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Validator;

trait Helper
{
    public static function validatorId(int $id,  string $namePk, string $nameTable)
    {
        // protecte your app from XSS by laravel_validation system
        $validator = Validator::make(
            ['id' => $id],
            [
                'id' => 'required|integer|exists:' . $nameTable . ',' . $namePk,
            ],
            [
                'id.exists' => $namePk . ' not found',
                'id.required' => $namePk . ' ID is required',
                'id.integer' => 'Invalid ' . $namePk . ' format',
            ]
        );

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first('id'));
        }
    }
}

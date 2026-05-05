<?php

namespace App\Constants;

class ApiMessages
{
    public const successMessages = [
        'create' => 'Resource created successfully.',
        'read'   => 'Resource retrieved successfully.',
        'update' => 'Resource updated successfully.',
        'delete' => 'Resource deleted successfully.',
        'login'  => 'You have logged in successfully.',
        'register'=> 'Your account has been created successfully.',
        'logout' => 'You have been logged out successfully.',
    ];

    public const failledMessages = [
        'create' => 'Failed to create resource.',
        'read'   => 'Failed to retrieve resource.',
        'update' => 'Failed to update resource.',
        'delete' => 'Failed to delete resource.',
        'login'  => 'The email or password you entered is incorrect.',
        'register' => 'Registration could not be completed. Please try again.',
        'logout' => 'Invalid or expired session token.',
        'auth' => 'Unauthenticated.',
        'authorization' => 'You are not allowed to perform this.'
    ];
}

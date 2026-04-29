<?php

namespace App\Constants;

class ApiMessages
{
    public const successMessages = [
        'create' => 'Resource created successfully.',
        'read'   => 'Resource retrieved successfully.',
        'update' => 'Resource updated successfully.',
        'delete' => 'Resource deleted successfully.',
    ];

    public const failledMessages = [
        'create' => 'Failed to create resource.',
        'read'   => 'Failed to retrieve resource.',
        'update' => 'Failed to update resource.',
        'delete' => 'Failed to delete resource.',
    ];
}

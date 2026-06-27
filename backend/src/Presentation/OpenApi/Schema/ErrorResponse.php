<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['error'],
    properties: [
        new OA\Property(property: 'error', type: 'string', example: 'Invalid request'),
    ],
)]
final class ErrorResponse
{
}

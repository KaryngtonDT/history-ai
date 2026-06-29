<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatStreamToken',
    required: ['index', 'text'],
    properties: [
        new OA\Property(
            property: 'index',
            type: 'integer',
            minimum: 0,
            example: 0,
        ),
        new OA\Property(
            property: 'text',
            type: 'string',
            minLength: 1,
            example: 'Mock ',
        ),
    ],
)]
final class ChatStreamToken
{
}

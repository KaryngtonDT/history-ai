<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatRequest',
    required: ['question'],
    properties: [
        new OA\Property(
            property: 'question',
            type: 'string',
            minLength: 1,
            maxLength: 2000,
            example: 'Why did Rome collapse?',
        ),
    ],
)]
final class ChatRequest
{
}

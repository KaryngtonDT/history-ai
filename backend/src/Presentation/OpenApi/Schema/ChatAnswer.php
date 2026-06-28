<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatAnswer',
    required: ['answer', 'sources'],
    properties: [
        new OA\Property(
            property: 'answer',
            type: 'string',
            example: 'Mock answer based on retrieved context.',
        ),
        new OA\Property(
            property: 'sources',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ChatSource'),
        ),
    ],
)]
final class ChatAnswer
{
}

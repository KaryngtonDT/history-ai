<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChatAnswer',
    required: ['answer', 'sources', 'citations'],
    properties: [
        new OA\Property(
            property: 'answer',
            type: 'string',
            example: 'Mock answer based on retrieved context [1].',
        ),
        new OA\Property(
            property: 'sources',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ChatSource'),
        ),
        new OA\Property(
            property: 'citations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ChatCitation'),
        ),
    ],
)]
final class ChatAnswer
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Conversation',
    required: ['id', 'contentId', 'messages'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440001',
        ),
        new OA\Property(
            property: 'contentId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440000',
        ),
        new OA\Property(
            property: 'messages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ConversationMessage'),
        ),
    ],
)]
final class Conversation
{
}

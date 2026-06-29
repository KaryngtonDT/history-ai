<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateConversationDocumentsRequest',
    required: ['contentIds'],
    properties: [
        new OA\Property(
            property: 'contentIds',
            type: 'array',
            minItems: 1,
            items: new OA\Items(
                type: 'string',
                format: 'uuid',
                example: '550e8400-e29b-41d4-a716-446655440000',
            ),
            example: [
                '550e8400-e29b-41d4-a716-446655440000',
                '550e8400-e29b-41d4-a716-446655440099',
            ],
        ),
    ],
)]
final class UpdateConversationDocumentsRequest
{
}

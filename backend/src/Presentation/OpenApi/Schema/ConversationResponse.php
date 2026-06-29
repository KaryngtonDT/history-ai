<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConversationResponse',
    required: ['conversation'],
    properties: [
        new OA\Property(
            property: 'conversation',
            ref: '#/components/schemas/Conversation',
        ),
    ],
)]
final class ConversationResponse
{
}

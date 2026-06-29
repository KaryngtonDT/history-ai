<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConversationChatResponse',
    required: ['conversation', 'answer'],
    properties: [
        new OA\Property(
            property: 'conversation',
            ref: '#/components/schemas/Conversation',
        ),
        new OA\Property(
            property: 'answer',
            ref: '#/components/schemas/ChatAnswer',
        ),
    ],
)]
final class ConversationChatResponse
{
}

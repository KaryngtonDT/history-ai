<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConversationMessage',
    required: ['role', 'text'],
    properties: [
        new OA\Property(
            property: 'role',
            type: 'string',
            enum: ['user', 'assistant'],
            example: 'user',
        ),
        new OA\Property(
            property: 'text',
            type: 'string',
            example: 'Why did Rome collapse?',
        ),
    ],
)]
final class ConversationMessage
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AIProvider',
    required: ['providerId', 'displayName', 'capability', 'enabled'],
    properties: [
        new OA\Property(property: 'providerId', type: 'string', example: 'faster_whisper'),
        new OA\Property(property: 'displayName', type: 'string', example: 'Faster Whisper'),
        new OA\Property(
            property: 'capability',
            ref: '#/components/schemas/AIEngineCapability',
        ),
        new OA\Property(property: 'enabled', type: 'boolean', example: true),
    ],
)]
final class AIProviderSchema
{
}

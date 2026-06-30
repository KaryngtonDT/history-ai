<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AIEngine',
    required: ['engineId', 'capability', 'enabled', 'providers'],
    properties: [
        new OA\Property(property: 'engineId', type: 'string', example: 'speech-to-text'),
        new OA\Property(
            property: 'capability',
            ref: '#/components/schemas/AIEngineCapability',
        ),
        new OA\Property(property: 'enabled', type: 'boolean', example: true),
        new OA\Property(
            property: 'providers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AIProvider'),
        ),
    ],
)]
final class AIEngineSchema
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AIProvidersList',
    required: ['engines'],
    properties: [
        new OA\Property(
            property: 'engines',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AIEngine'),
        ),
    ],
)]
final class AIProvidersListSchema
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PreferenceProfile',
    required: [
        'translationStyle',
        'voiceStability',
        'renderingPreset',
        'lipSyncStrength',
        'latestComment',
        'reviewCount',
        'explanationLines',
    ],
    properties: [
        new OA\Property(property: 'translationStyle', type: 'string', example: 'natural'),
        new OA\Property(property: 'voiceStability', type: 'string', example: 'high'),
        new OA\Property(property: 'renderingPreset', type: 'string', example: 'quality'),
        new OA\Property(property: 'lipSyncStrength', type: 'string', example: 'subtle'),
        new OA\Property(property: 'latestComment', type: 'string'),
        new OA\Property(property: 'reviewCount', type: 'integer', minimum: 0),
        new OA\Property(
            property: 'explanationLines',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
final class PreferenceProfileSchema
{
}

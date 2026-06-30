<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenerateVideoRenderRequest',
    properties: [
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TranslationLanguage'),
        ),
        new OA\Property(property: 'provider', ref: '#/components/schemas/VideoRenderProvider'),
        new OA\Property(property: 'format', ref: '#/components/schemas/VideoRenderFormat'),
        new OA\Property(property: 'quality', ref: '#/components/schemas/VideoRenderQuality'),
    ],
)]
final class GenerateVideoRenderRequestSchema
{
}

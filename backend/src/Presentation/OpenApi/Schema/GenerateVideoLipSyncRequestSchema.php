<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenerateVideoLipSyncRequest',
    properties: [
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TranslationLanguage'),
        ),
        new OA\Property(property: 'provider', ref: '#/components/schemas/LipSyncProvider'),
    ],
)]
final class GenerateVideoLipSyncRequestSchema
{
}

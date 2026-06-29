<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GenerateVideoTranslationsRequest',
    required: ['targetLanguages'],
    properties: [
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TranslationLanguage'),
            example: ['french', 'german'],
        ),
        new OA\Property(
            property: 'provider',
            ref: '#/components/schemas/TranslationProvider',
            nullable: true,
        ),
    ],
)]
final class GenerateVideoTranslationsRequestSchema
{
}

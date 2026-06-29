<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranslationLanguage',
    type: 'string',
    description: 'Source or target language for a translated transcript.',
    enum: ['english', 'french', 'german', 'spanish', 'italian', 'unknown'],
    example: 'french',
)]
final class TranslationLanguageSchema
{
}

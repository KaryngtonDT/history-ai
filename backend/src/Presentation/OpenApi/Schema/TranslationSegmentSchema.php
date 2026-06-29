<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranslationSegment',
    required: ['index', 'sourceText', 'translatedText'],
    properties: [
        new OA\Property(property: 'index', type: 'integer', minimum: 0, example: 0),
        new OA\Property(property: 'sourceText', type: 'string', example: 'Hello everyone'),
        new OA\Property(property: 'translatedText', type: 'string', example: 'Bonjour tout le monde'),
    ],
)]
final class TranslationSegmentSchema
{
}

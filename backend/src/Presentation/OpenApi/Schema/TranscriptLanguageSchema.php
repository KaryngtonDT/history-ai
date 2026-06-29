<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranscriptLanguage',
    type: 'string',
    description: 'Detected or assigned language of a speech-to-text transcript.',
    enum: ['english', 'french', 'german', 'unknown'],
    example: 'english',
)]
final class TranscriptLanguageSchema
{
}

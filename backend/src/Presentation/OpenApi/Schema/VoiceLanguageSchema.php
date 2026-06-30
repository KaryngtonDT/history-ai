<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoiceLanguage',
    type: 'string',
    enum: ['english', 'french', 'german', 'spanish', 'italian'],
    example: 'french',
)]
final class VoiceLanguageSchema
{
}

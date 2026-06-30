<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TextToSpeechProvider',
    type: 'string',
    enum: ['f5_tts', 'kokoro', 'xtts', 'mock'],
    example: 'f5_tts',
)]
final class TextToSpeechProviderSchema
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranslationProvider',
    type: 'string',
    description: 'AI translation engine used to produce the translated transcript.',
    enum: ['qwen', 'deepseek', 'gemini', 'gpt', 'mock'],
    example: 'qwen',
)]
final class TranslationProviderSchema
{
}

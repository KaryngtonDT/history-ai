<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VoiceCloneProvider',
    type: 'string',
    enum: ['openvoice', 'seedvc', 'mock'],
    example: 'openvoice',
)]
final class VoiceCloneProviderSchema
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LipSyncProvider',
    type: 'string',
    enum: ['latentsync', 'wav2lip', 'mock'],
    example: 'latentsync',
)]
final class LipSyncProviderSchema
{
}

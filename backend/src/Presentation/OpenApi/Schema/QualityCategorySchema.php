<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QualityCategory',
    type: 'string',
    enum: ['audio', 'translation', 'voice_clone', 'lip_sync', 'rendering'],
    example: 'audio',
)]
final class QualityCategorySchema
{
}

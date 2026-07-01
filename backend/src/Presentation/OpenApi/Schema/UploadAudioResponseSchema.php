<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UploadAudioResponse',
    required: ['audioId', 'status'],
    properties: [
        new OA\Property(
            property: 'audioId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440088',
        ),
        new OA\Property(
            property: 'status',
            ref: '#/components/schemas/SourceStatus',
        ),
    ],
)]
final class UploadAudioResponseSchema
{
}

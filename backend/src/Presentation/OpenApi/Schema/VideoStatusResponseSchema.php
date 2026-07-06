<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoStatusResponse',
    required: ['videoId', 'status', 'originalFilename', 'language', 'createdAt'],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'status', ref: '#/components/schemas/VideoStatus'),
        new OA\Property(property: 'originalFilename', type: 'string'),
        new OA\Property(property: 'language', type: 'string'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(property: 'failureMessage', type: 'string', nullable: true, description: 'Last pipeline error when status is failed.'),
        new OA\Property(property: 'failedStage', type: 'string', nullable: true, description: 'Pipeline stage that failed (e.g. speech_to_text).'),
        new OA\Property(property: 'lastProcessingDurationSeconds', type: 'number', format: 'float', nullable: true),
    ],
)]
final class VideoStatusResponseSchema
{
}

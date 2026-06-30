<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ScheduledStage',
    required: [
        'stage',
        'order',
        'status',
        'estimatedDurationSeconds',
        'parallelGroup',
        'requirements',
    ],
    properties: [
        new OA\Property(
            property: 'stage',
            type: 'string',
            enum: [
                'speech_to_text',
                'translation',
                'text_to_speech',
                'voice_clone',
                'lip_sync',
                'video_render',
            ],
            example: 'voice_clone',
        ),
        new OA\Property(property: 'order', type: 'integer', minimum: 1, example: 4),
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['pending', 'running', 'completed', 'failed'],
            example: 'running',
        ),
        new OA\Property(property: 'estimatedDurationSeconds', type: 'integer', minimum: 1, example: 120),
        new OA\Property(property: 'parallelGroup', type: 'integer', minimum: 1, example: 4),
        new OA\Property(
            property: 'requirements',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ResourceRequirement'),
        ),
    ],
)]
final class ScheduledStageSchema
{
}

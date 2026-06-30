<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OptimizationStage',
    required: ['stage', 'parameters'],
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
            example: 'speech_to_text',
        ),
        new OA\Property(
            property: 'parameters',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OptimizationParameter'),
        ),
        new OA\Property(
            property: 'explanations',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
final class OptimizationStageSchema
{
}

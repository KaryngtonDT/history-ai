<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoIntelligence',
    required: [
        'id',
        'videoId',
        'durationSeconds',
        'scene',
        'audio',
        'visual',
        'speech',
        'speakers',
        'gpuAvailable',
        'estimatedVramGb',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'durationSeconds', type: 'number', format: 'float', example: 762.0),
        new OA\Property(property: 'scene', type: 'string', example: 'interview'),
        new OA\Property(property: 'audio', ref: '#/components/schemas/AudioCharacteristics'),
        new OA\Property(property: 'visual', ref: '#/components/schemas/VisualCharacteristics'),
        new OA\Property(property: 'speech', ref: '#/components/schemas/SpeechCharacteristics'),
        new OA\Property(
            property: 'speakers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/VideoSpeaker'),
        ),
        new OA\Property(property: 'gpuAvailable', type: 'boolean', example: true),
        new OA\Property(property: 'estimatedVramGb', type: 'number', format: 'float', example: 8.0),
    ],
)]
final class VideoIntelligenceSchema
{
}

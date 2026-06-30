<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VideoSpeaker',
    required: ['index', 'label'],
    properties: [
        new OA\Property(property: 'index', type: 'integer', example: 1),
        new OA\Property(property: 'label', type: 'string', example: 'Speaker 1'),
    ],
)]
final class VideoSpeakerSchema
{
}

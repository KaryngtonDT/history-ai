<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ExecutionSchedule',
    required: [
        'id',
        'videoId',
        'strategy',
        'estimatedCompletionSeconds',
        'stages',
        'resources',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'strategy', ref: '#/components/schemas/SchedulingStrategy'),
        new OA\Property(property: 'estimatedCompletionSeconds', type: 'integer', minimum: 1, example: 360),
        new OA\Property(property: 'currentStage', type: 'string', nullable: true, example: 'voice_clone'),
        new OA\Property(property: 'currentResource', ref: '#/components/schemas/ResourceType', nullable: true),
        new OA\Property(
            property: 'stages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ScheduledStage'),
        ),
        new OA\Property(
            property: 'resources',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ExecutionResource'),
        ),
    ],
)]
final class ExecutionScheduleSchema
{
}

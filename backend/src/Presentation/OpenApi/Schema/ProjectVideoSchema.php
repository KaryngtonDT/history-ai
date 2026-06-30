<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProjectVideo',
    required: ['videoId', 'filename', 'addedAt'],
    properties: [
        new OA\Property(property: 'videoId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'filename', type: 'string', example: 'Interview.mp4'),
        new OA\Property(property: 'addedAt', type: 'string', format: 'date-time'),
    ],
)]
final class ProjectVideoSchema
{
}

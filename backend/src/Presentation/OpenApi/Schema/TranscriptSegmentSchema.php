<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TranscriptSegment',
    required: ['index', 'startTime', 'endTime', 'text'],
    properties: [
        new OA\Property(property: 'index', type: 'integer', minimum: 0, example: 0),
        new OA\Property(property: 'startTime', type: 'number', format: 'float', minimum: 0, example: 0.0),
        new OA\Property(property: 'endTime', type: 'number', format: 'float', minimum: 0, example: 2.5),
        new OA\Property(property: 'text', type: 'string', example: 'Hello world'),
    ],
)]
final class TranscriptSegmentSchema
{
}

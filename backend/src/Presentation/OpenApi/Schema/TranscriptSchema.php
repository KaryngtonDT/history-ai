<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Transcript',
    required: [
        'videoId',
        'transcriptId',
        'language',
        'text',
        'duration',
        'segmentCount',
        'segments',
    ],
    properties: [
        new OA\Property(
            property: 'videoId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440099',
        ),
        new OA\Property(
            property: 'transcriptId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440010',
        ),
        new OA\Property(
            property: 'language',
            ref: '#/components/schemas/TranscriptLanguage',
        ),
        new OA\Property(property: 'text', type: 'string', example: 'Hello world'),
        new OA\Property(property: 'duration', type: 'number', format: 'float', example: 2.5),
        new OA\Property(property: 'segmentCount', type: 'integer', minimum: 0, example: 1),
        new OA\Property(
            property: 'segments',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TranscriptSegment'),
        ),
    ],
)]
final class TranscriptSchema
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Project',
    required: [
        'id',
        'name',
        'createdAt',
        'videos',
        'batchJobId',
        'batchStatus',
        'batchProgress',
        'targetLanguages',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string', example: 'Marketing Campaign'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'videos',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ProjectVideo'),
        ),
        new OA\Property(property: 'batchJobId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'batchStatus', ref: '#/components/schemas/BatchJobStatus', nullable: true),
        new OA\Property(property: 'batchProgress', type: 'integer', minimum: 0, maximum: 100),
        new OA\Property(
            property: 'targetLanguages',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['fr', 'de'],
        ),
    ],
)]
final class ProjectSchema
{
}

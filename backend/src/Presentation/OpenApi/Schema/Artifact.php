<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Artifact',
    required: ['id', 'contentId', 'processingJobId', 'type', 'content', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'contentId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'processingJobId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', ref: '#/components/schemas/ArtifactType'),
        new OA\Property(
            property: 'content',
            type: 'string',
            description: 'Markdown or structured text produced by the worker.',
            example: "# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome",
        ),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
    ],
)]
final class Artifact
{
}

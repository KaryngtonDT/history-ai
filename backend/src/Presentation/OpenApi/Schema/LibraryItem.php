<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LibraryItem',
    required: ['id', 'contentId', 'artifactId', 'type', 'title', 'createdAt'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'contentId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'artifactId', type: 'string', format: 'uuid'),
        new OA\Property(property: 'type', ref: '#/components/schemas/LibraryItemType'),
        new OA\Property(property: 'title', type: 'string', example: 'Timeline'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
    ],
)]
final class LibraryItem
{
}

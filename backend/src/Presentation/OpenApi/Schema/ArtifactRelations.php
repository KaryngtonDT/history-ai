<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArtifactRelations',
    required: ['relations'],
    properties: [
        new OA\Property(
            property: 'relations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ArtifactRelation'),
        ),
    ],
)]
final class ArtifactRelations
{
}

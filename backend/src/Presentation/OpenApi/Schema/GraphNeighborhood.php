<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GraphNeighborhood',
    required: ['center', 'neighbors', 'edges'],
    properties: [
        new OA\Property(
            property: 'center',
            ref: '#/components/schemas/GraphNeighborhoodNode',
        ),
        new OA\Property(
            property: 'neighbors',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/GraphNeighborhoodNode'),
        ),
        new OA\Property(
            property: 'edges',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/GraphEdge'),
        ),
    ],
)]
final class GraphNeighborhood
{
}

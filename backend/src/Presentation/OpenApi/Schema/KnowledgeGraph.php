<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'KnowledgeGraph',
    required: ['nodes', 'edges'],
    properties: [
        new OA\Property(
            property: 'nodes',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/GraphNode'),
        ),
        new OA\Property(
            property: 'edges',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/GraphEdge'),
        ),
    ],
)]
final class KnowledgeGraph
{
}

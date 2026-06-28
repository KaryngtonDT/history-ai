<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SemanticSearchResult',
    required: ['results'],
    properties: [
        new OA\Property(
            property: 'results',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/RetrievedChunk'),
        ),
    ],
)]
final class SemanticSearchResult
{
}

<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ArtifactRelationType',
    type: 'string',
    description: 'Semantic type describing how two artifacts are related.',
    enum: ['related', 'derived_from', 'references', 'next', 'previous'],
    example: 'derived_from',
)]
final class ArtifactRelationTypeSchema
{
}

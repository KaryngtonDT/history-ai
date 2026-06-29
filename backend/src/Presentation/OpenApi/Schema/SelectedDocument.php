<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SelectedDocument',
    required: ['contentId'],
    properties: [
        new OA\Property(
            property: 'contentId',
            type: 'string',
            format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440000',
        ),
    ],
)]
final class SelectedDocument
{
}

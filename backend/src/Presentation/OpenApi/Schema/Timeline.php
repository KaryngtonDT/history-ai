<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Timeline',
    required: ['sections'],
    properties: [
        new OA\Property(
            property: 'sections',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TimelineSection'),
        ),
    ],
)]
final class Timeline
{
}

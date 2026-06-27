<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TimelineSection',
    required: ['title', 'events'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Ancient Rome'),
        new OA\Property(
            property: 'events',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TimelineEvent'),
        ),
    ],
)]
final class TimelineSection
{
}

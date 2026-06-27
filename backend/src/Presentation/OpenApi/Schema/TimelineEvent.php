<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TimelineEvent',
    required: ['text'],
    properties: [
        new OA\Property(
            property: 'text',
            type: 'string',
            example: '753 BC — Foundation of Rome',
        ),
    ],
)]
final class TimelineEvent
{
}

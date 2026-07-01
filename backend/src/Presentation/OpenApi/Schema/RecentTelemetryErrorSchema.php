<?php

declare(strict_types=1);

namespace App\Presentation\OpenApi\Schema;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RecentTelemetryError',
    required: ['message', 'status', 'recordedAt'],
    properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'recordedAt', type: 'string', format: 'date-time'),
    ],
)]
final class RecentTelemetryErrorSchema
{
}

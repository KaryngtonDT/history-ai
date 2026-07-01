<?php

declare(strict_types=1);

namespace App\Application\Telemetry\Queries;

final readonly class GetProviderStatisticsQuery
{
    public function __construct(public string $workspaceId)
    {
    }
}

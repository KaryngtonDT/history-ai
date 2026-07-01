<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\DTO\WorkspaceAnalyticsResult;
use App\Application\Telemetry\Queries\GetWorkspaceAnalyticsQuery;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;

final class GetWorkspaceAnalyticsHandler
{
    public function __construct(
        private readonly PipelineTelemetryRepositoryInterface $telemetryRepository,
        private readonly WorkspaceAnalyticsAggregator $aggregator,
    ) {
    }

    public function __invoke(GetWorkspaceAnalyticsQuery $query): WorkspaceAnalyticsResult
    {
        $records = $this->telemetryRepository->findByWorkspaceId($query->workspaceId);

        return $this->aggregator->aggregate($query->workspaceId, $records);
    }
}

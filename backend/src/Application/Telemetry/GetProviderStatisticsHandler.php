<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\DTO\ProviderStatisticsResult;
use App\Application\Telemetry\Queries\GetProviderStatisticsQuery;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;

final class GetProviderStatisticsHandler
{
    public function __construct(
        private readonly PipelineTelemetryRepositoryInterface $telemetryRepository,
        private readonly WorkspaceAnalyticsAggregator $aggregator,
    ) {
    }

    public function __invoke(GetProviderStatisticsQuery $query): ProviderStatisticsResult
    {
        $records = $this->telemetryRepository->findByWorkspaceId($query->workspaceId);

        return $this->aggregator->providerStatistics($records);
    }
}

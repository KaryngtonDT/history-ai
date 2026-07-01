<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\Commands\CollectPipelineMetricsCommand;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;

final class CollectPipelineMetricsHandler
{
    public function __construct(
        private readonly PipelineTelemetryRepositoryInterface $telemetryRepository,
    ) {
    }

    public function __invoke(CollectPipelineMetricsCommand $command): void
    {
        $this->telemetryRepository->append($command->telemetry);
    }
}

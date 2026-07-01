<?php

declare(strict_types=1);

namespace App\Application\Telemetry\Commands;

use App\Domain\Telemetry\PipelineTelemetry;

final readonly class CollectPipelineMetricsCommand
{
    public function __construct(public PipelineTelemetry $telemetry)
    {
    }
}

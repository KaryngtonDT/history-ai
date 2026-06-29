<?php

declare(strict_types=1);

namespace App\Application\Platform;

interface PerformanceMetricsRecorderInterface
{
    public function record(PerformanceMetricCollection $metrics): void;
}

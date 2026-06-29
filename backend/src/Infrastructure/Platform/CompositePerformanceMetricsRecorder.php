<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\PerformanceMetricCollection;
use App\Application\Platform\PerformanceMetricsRecorderInterface;

final class CompositePerformanceMetricsRecorder implements PerformanceMetricsRecorderInterface
{
    /**
     * @param list<PerformanceMetricsRecorderInterface> $recorders
     */
    public function __construct(
        private readonly array $recorders,
    ) {
    }

    public function record(PerformanceMetricCollection $metrics): void
    {
        foreach ($this->recorders as $recorder) {
            $recorder->record($metrics);
        }
    }
}

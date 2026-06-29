<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform\Support;

use App\Application\Platform\PerformanceMetricCollection;
use App\Application\Platform\PerformanceMetricsRecorderInterface;

final class RecordingPerformanceMetricsRecorder implements PerformanceMetricsRecorderInterface
{
    /** @var list<PerformanceMetricCollection> */
    private array $recordings = [];

    public function record(PerformanceMetricCollection $metrics): void
    {
        $this->recordings[] = $metrics;
    }

    /**
     * @return list<PerformanceMetricCollection>
     */
    public function recordings(): array
    {
        return $this->recordings;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\PerformanceMetricCollection;
use App\Application\Platform\PerformanceMetricsRecorderInterface;
use App\Application\Platform\PlatformLoggerInterface;

final class LoggingPerformanceMetricsRecorder implements PerformanceMetricsRecorderInterface
{
    private const string COMPONENT = 'PerformanceMetrics';

    public function __construct(
        private readonly PlatformLoggerInterface $platformLogger,
    ) {
    }

    public function record(PerformanceMetricCollection $metrics): void
    {
        if ($metrics->isEmpty()) {
            return;
        }

        /** @var array<string, int> $context */
        $context = [];

        foreach ($metrics->metrics() as $metric) {
            $context[$metric->name] = $metric->durationMs;
        }

        $this->platformLogger->info(self::COMPONENT, 'performance metrics recorded', $context);
    }
}

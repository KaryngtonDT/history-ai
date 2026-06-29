<?php

declare(strict_types=1);

namespace App\Application\Platform;

final readonly class PerformanceMetricSnapshot
{
    public function __construct(
        public string $correlationId,
        public string $recordedAt,
        public PerformanceMetricCollection $metrics,
    ) {
    }
}

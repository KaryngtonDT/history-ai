<?php

declare(strict_types=1);

namespace App\Application\Platform;

interface PerformanceMetricsReaderInterface
{
    public function recent(int $limit = 20): PerformanceMetricSnapshotCollection;
}

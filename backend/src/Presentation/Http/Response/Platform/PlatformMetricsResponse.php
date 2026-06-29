<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Platform;

use App\Application\Platform\PerformanceMetric;
use App\Application\Platform\PerformanceMetricSnapshot;
use App\Application\Platform\PerformanceMetricSnapshotCollection;

final class PlatformMetricsResponse
{
    /**
     * @return array{
     *     snapshots: list<array{
     *         correlationId: string,
     *         recordedAt: string,
     *         metrics: list<array{name: string, durationMs: int}>
     *     }>
     * }
     */
    public static function fromCollection(PerformanceMetricSnapshotCollection $collection): array
    {
        return [
            'snapshots' => array_map(
                static fn (PerformanceMetricSnapshot $snapshot): array => [
                    'correlationId' => $snapshot->correlationId,
                    'recordedAt' => $snapshot->recordedAt,
                    'metrics' => array_map(
                        static fn (PerformanceMetric $metric): array => [
                            'name' => $metric->name,
                            'durationMs' => $metric->durationMs,
                        ],
                        $snapshot->metrics->metrics(),
                    ),
                ],
                $collection->snapshots(),
            ),
        ];
    }
}

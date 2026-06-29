<?php

declare(strict_types=1);

namespace App\Infrastructure\Platform;

use App\Application\Platform\ClockInterface;
use App\Application\Platform\PerformanceMetricCollection;
use App\Application\Platform\PerformanceMetricSnapshot;
use App\Application\Platform\PerformanceMetricSnapshotCollection;
use App\Application\Platform\PerformanceMetricsReaderInterface;
use App\Application\Platform\PerformanceMetricsRecorderInterface;
use App\Application\Platform\RequestContextProviderInterface;

final class InMemoryPerformanceMetricsStore implements PerformanceMetricsRecorderInterface, PerformanceMetricsReaderInterface
{
    /** @var list<PerformanceMetricSnapshot> */
    private array $snapshots = [];

    public function __construct(
        private readonly RequestContextProviderInterface $requestContextProvider,
        private readonly ClockInterface $clock,
        private readonly int $maxSize = 100,
    ) {
        if ($maxSize < 1) {
            throw new \InvalidArgumentException('Max size must be at least 1.');
        }
    }

    public function record(PerformanceMetricCollection $metrics): void
    {
        if ($metrics->isEmpty()) {
            return;
        }

        $this->snapshots[] = new PerformanceMetricSnapshot(
            $this->requestContextProvider->getContext()->correlationId->value,
            $this->formatRecordedAt(),
            $metrics,
        );

        if (count($this->snapshots) > $this->maxSize) {
            array_shift($this->snapshots);
        }
    }

    public function recent(int $limit = 20): PerformanceMetricSnapshotCollection
    {
        if ($limit < 1) {
            return PerformanceMetricSnapshotCollection::empty();
        }

        if ([] === $this->snapshots) {
            return PerformanceMetricSnapshotCollection::empty();
        }

        $slice = array_slice($this->snapshots, -$limit);

        return new PerformanceMetricSnapshotCollection(array_reverse($slice));
    }

    public function count(): int
    {
        return count($this->snapshots);
    }

    private function formatRecordedAt(): string
    {
        $dateTime = \DateTimeImmutable::createFromFormat(
            'U.u',
            sprintf('%.6F', $this->clock->now()),
            new \DateTimeZone('UTC'),
        );

        if (false === $dateTime) {
            throw new \RuntimeException('Unable to format performance metric timestamp.');
        }

        return $dateTime->format(\DateTimeInterface::ATOM);
    }
}

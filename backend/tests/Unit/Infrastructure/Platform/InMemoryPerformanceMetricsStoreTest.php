<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Platform;

use App\Application\Platform\PerformanceMetric;
use App\Application\Platform\PerformanceMetricCollection;
use App\Domain\Platform\CorrelationId;
use App\Infrastructure\Platform\InMemoryPerformanceMetricsStore;
use App\Tests\Unit\Application\Platform\Support\FixedClock;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use PHPUnit\Framework\TestCase;

final class InMemoryPerformanceMetricsStoreTest extends TestCase
{
    public function testStoresSnapshotsWithCorrelationIdAndRecordedAt(): void
    {
        $store = $this->createStore(
            new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'),
            new FixedClock(1_700_000_000.0),
        );

        $store->record(PerformanceMetricCollection::empty()
            ->with(new PerformanceMetric('chunking_ms', 4))
            ->with(new PerformanceMetric('total_ms', 10)));

        $snapshots = $store->recent()->snapshots();

        self::assertCount(1, $snapshots);
        self::assertSame('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d', $snapshots[0]->correlationId);
        self::assertSame('2023-11-14T22:13:20+00:00', $snapshots[0]->recordedAt);
        self::assertSame(['chunking_ms', 'total_ms'], $snapshots[0]->metrics->names());
    }

    public function testReturnsSnapshotsNewestFirst(): void
    {
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));
        $store = new InMemoryPerformanceMetricsStore(
            $contextProvider,
            new FixedClock(1_700_000_000.0),
            maxSize: 100,
        );

        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('chunking_ms', 1)));
        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('embedding_ms', 2)));
        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('total_ms', 3)));

        $names = array_map(
            static fn ($snapshot) => $snapshot->metrics->names()[0],
            $store->recent(3)->snapshots(),
        );

        self::assertSame(['total_ms', 'embedding_ms', 'chunking_ms'], $names);
    }

    public function testEnforcesMaxSizeAsRingBuffer(): void
    {
        $store = $this->createStore(
            new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'),
            new FixedClock(),
            maxSize: 2,
        );

        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('chunking_ms', 1)));
        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('embedding_ms', 2)));
        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('total_ms', 3)));

        self::assertSame(2, $store->count());

        $names = array_map(
            static fn ($snapshot) => $snapshot->metrics->names()[0],
            $store->recent(10)->snapshots(),
        );

        self::assertSame(['total_ms', 'embedding_ms'], $names);
    }

    public function testAppliesLimitWhenReadingRecentSnapshots(): void
    {
        $store = $this->createStore(
            new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'),
            new FixedClock(),
        );

        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('chunking_ms', 1)));
        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('embedding_ms', 2)));
        $store->record(PerformanceMetricCollection::empty()->with(new PerformanceMetric('total_ms', 3)));

        self::assertCount(2, $store->recent(2)->snapshots());
        self::assertCount(1, $store->recent(1)->snapshots());
        self::assertSame('total_ms', $store->recent(1)->snapshots()[0]->metrics->names()[0]);
    }

    public function testSkipsEmptyMetricCollections(): void
    {
        $store = $this->createStore(
            new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'),
            new FixedClock(),
        );

        $store->record(PerformanceMetricCollection::empty());

        self::assertSame(0, $store->count());
    }

    private function createStore(
        CorrelationId $correlationId,
        FixedClock $clock,
        int $maxSize = 100,
    ): InMemoryPerformanceMetricsStore {
        return new InMemoryPerformanceMetricsStore(
            new FixedRequestContextProvider($correlationId),
            $clock,
            $maxSize,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform;

use App\Application\Platform\PerformanceMetric;
use App\Application\Platform\PerformanceMetricCollection;
use PHPUnit\Framework\TestCase;

final class PerformanceMetricCollectionTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $collection = PerformanceMetricCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame([], $collection->metrics());
        self::assertSame([], $collection->names());
    }

    public function testPreservesInsertionOrder(): void
    {
        $collection = PerformanceMetricCollection::empty()
            ->with(new PerformanceMetric('chunking_ms', 1))
            ->with(new PerformanceMetric('embedding_ms', 2))
            ->with(new PerformanceMetric('total_ms', 3));

        self::assertSame(
            ['chunking_ms', 'embedding_ms', 'total_ms'],
            $collection->names(),
        );
    }

    public function testMergeAppendsMetricsInOrder(): void
    {
        $left = PerformanceMetricCollection::empty()
            ->with(new PerformanceMetric('chunking_ms', 1));
        $right = PerformanceMetricCollection::empty()
            ->with(new PerformanceMetric('embedding_ms', 2));

        $merged = $left->merge($right);

        self::assertSame(['chunking_ms', 'embedding_ms'], $merged->names());
    }
}

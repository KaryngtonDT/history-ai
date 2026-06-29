<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform;

use App\Application\Platform\PerformanceMetric;
use PHPUnit\Framework\TestCase;

final class PerformanceMetricTest extends TestCase
{
    public function testStoresNameAndDuration(): void
    {
        $metric = new PerformanceMetric('retrieval_ms', 12);

        self::assertSame('retrieval_ms', $metric->name);
        self::assertSame(12, $metric->durationMs);
    }

    public function testRejectsNegativeDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PerformanceMetric('total_ms', -1);
    }
}

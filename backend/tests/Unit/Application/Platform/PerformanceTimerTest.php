<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Platform;

use App\Application\Platform\PerformanceMetric;
use App\Application\Platform\PerformanceTimer;
use App\Tests\Unit\Application\Platform\Support\FixedClock;
use PHPUnit\Framework\TestCase;

final class PerformanceTimerTest extends TestCase
{
    public function testMeasuresDurationInMilliseconds(): void
    {
        $clock = new FixedClock(step: 0.025);
        $timer = new PerformanceTimer($clock);

        $timer->start();
        $metric = $timer->stop('chunking_ms');

        self::assertSame('chunking_ms', $metric->name);
        self::assertSame(25, $metric->durationMs);
    }

    public function testNeverReturnsNegativeDuration(): void
    {
        $clock = new FixedClock(step: 0.0);
        $timer = new PerformanceTimer($clock);

        $timer->start();
        $metric = $timer->stop('embedding_ms');

        self::assertSame(0, $metric->durationMs);
    }

    public function testThrowsWhenStoppedBeforeStart(): void
    {
        $timer = new PerformanceTimer(new FixedClock());

        $this->expectException(\LogicException::class);

        $timer->stop('total_ms');
    }
}

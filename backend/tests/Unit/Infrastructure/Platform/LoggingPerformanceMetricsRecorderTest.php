<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Platform;

use App\Application\Platform\PerformanceMetric;
use App\Application\Platform\PerformanceMetricCollection;
use App\Domain\Platform\CorrelationId;
use App\Infrastructure\Platform\LoggingPerformanceMetricsRecorder;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use App\Tests\Unit\Application\Platform\Support\RecordingPlatformLogger;
use PHPUnit\Framework\TestCase;

final class LoggingPerformanceMetricsRecorderTest extends TestCase
{
    public function testWritesMetricsThroughPlatformLoggerWithCorrelationId(): void
    {
        $correlationId = new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d');
        $contextProvider = new FixedRequestContextProvider($correlationId);
        $platformLogger = new RecordingPlatformLogger($contextProvider);
        $recorder = new LoggingPerformanceMetricsRecorder($platformLogger);

        $recorder->record(PerformanceMetricCollection::empty()
            ->with(new PerformanceMetric('chunking_ms', 4))
            ->with(new PerformanceMetric('total_ms', 10)));

        self::assertCount(1, $platformLogger->records());
        self::assertSame('PerformanceMetrics', $platformLogger->records()[0]['component']);
        self::assertSame('performance metrics recorded', $platformLogger->records()[0]['message']);
        self::assertSame(4, $platformLogger->records()[0]['context']['chunking_ms']);
        self::assertSame(10, $platformLogger->records()[0]['context']['total_ms']);
        self::assertSame($correlationId->value, $platformLogger->records()[0]['context']['correlationId']);
    }

    public function testSkipsLoggingWhenCollectionIsEmpty(): void
    {
        $platformLogger = new RecordingPlatformLogger(
            new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d')),
        );
        $recorder = new LoggingPerformanceMetricsRecorder($platformLogger);

        $recorder->record(PerformanceMetricCollection::empty());

        self::assertSame([], $platformLogger->records());
    }
}

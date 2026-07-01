<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Telemetry;

use App\Domain\Telemetry\Exception\InvalidPipelineTelemetryException;
use App\Domain\Telemetry\ExecutionMetric;
use App\Domain\Telemetry\ExecutionMetricCollection;
use App\Domain\Telemetry\ExecutionMetricType;
use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\PipelineTelemetryId;
use App\Domain\Telemetry\ProviderUsage;
use App\Domain\Telemetry\ProviderUsageCollection;
use PHPUnit\Framework\TestCase;

final class TelemetryDomainTest extends TestCase
{
    public function testExecutionMetricUsesDefaultUnit(): void
    {
        $metric = ExecutionMetric::of(ExecutionMetricType::ProcessingTime, 292.0);

        self::assertSame('seconds', $metric->unit());
        self::assertSame(292.0, $metric->value());
    }

    public function testExecutionMetricRejectsNegativeValue(): void
    {
        $this->expectException(InvalidPipelineTelemetryException::class);

        ExecutionMetric::of(ExecutionMetricType::GpuUsage, -1.0);
    }

    public function testExecutionMetricCollectionAveragesByType(): void
    {
        $collection = ExecutionMetricCollection::empty()
            ->append(ExecutionMetric::of(ExecutionMetricType::ProcessingTime, 100.0))
            ->append(ExecutionMetric::of(ExecutionMetricType::ProcessingTime, 200.0))
            ->append(ExecutionMetric::of(ExecutionMetricType::GpuUsage, 70.0));

        self::assertSame(150.0, $collection->averageForType(ExecutionMetricType::ProcessingTime));
        self::assertSame(70.0, $collection->averageForType(ExecutionMetricType::GpuUsage));
    }

    public function testProviderUsageCollectionMergesDuplicateProviders(): void
    {
        $collection = ProviderUsageCollection::empty()
            ->append(ProviderUsage::create('translation', 'ollama', 1, 10.0))
            ->append(ProviderUsage::create('translation', 'ollama', 2, 5.0));

        self::assertSame(1, $collection->count());
        self::assertSame(3, $collection->topByInvocations()?->invocationCount());
        self::assertSame(15.0, $collection->topByInvocations()?->totalDurationSeconds());
    }

    public function testProviderUsageCollectionRejectsDuplicatesInConstructor(): void
    {
        $this->expectException(InvalidPipelineTelemetryException::class);

        new ProviderUsageCollection([
            ProviderUsage::create('tts', 'f5_tts'),
            ProviderUsage::create('tts', 'f5_tts'),
        ]);
    }

    public function testPipelineTelemetryStoresMetricsAndProviders(): void
    {
        $workspaceId = '550e8400-e29b-41d4-a716-446655490001';
        $videoId = '550e8400-e29b-41d4-a716-446655490010';

        $telemetry = PipelineTelemetry::create(
            PipelineTelemetryId::generate(),
            $workspaceId,
            $videoId,
            true,
            [
                ExecutionMetric::of(ExecutionMetricType::ProcessingTime, 292.0),
                ExecutionMetric::of(ExecutionMetricType::GpuUsage, 71.0),
                ExecutionMetric::of(ExecutionMetricType::RetryCount, 1.0),
            ],
            [
                ProviderUsage::create('translation', 'ollama', 1, 45.0),
                ProviderUsage::create('text_to_speech', 'f5_tts', 1, 30.0),
            ],
            qualityScore: 94,
        );

        self::assertTrue($telemetry->success());
        self::assertSame(94, $telemetry->qualityScore());
        self::assertSame(292.0, $telemetry->processingTimeSeconds());
        self::assertSame(1, $telemetry->retryCount());
        self::assertSame(2, $telemetry->providerUsages()->count());
    }

    public function testPipelineTelemetryRejectsInvalidQualityScore(): void
    {
        $this->expectException(InvalidPipelineTelemetryException::class);

        PipelineTelemetry::create(
            PipelineTelemetryId::generate(),
            '550e8400-e29b-41d4-a716-446655490001',
            '550e8400-e29b-41d4-a716-446655490010',
            false,
            [],
            [],
            qualityScore: 120,
        );
    }
}

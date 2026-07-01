<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Telemetry;

use App\Application\Telemetry\CollectPipelineMetricsHandler;
use App\Application\Telemetry\Commands\CollectPipelineMetricsCommand;
use App\Application\Telemetry\GetProviderStatisticsHandler;
use App\Application\Telemetry\GetWorkspaceAnalyticsHandler;
use App\Application\Telemetry\Queries\GetProviderStatisticsQuery;
use App\Application\Telemetry\Queries\GetWorkspaceAnalyticsQuery;
use App\Application\Telemetry\WorkspaceAnalyticsAggregator;
use App\Domain\Telemetry\ExecutionMetric;
use App\Domain\Telemetry\ExecutionMetricType;
use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\PipelineTelemetryId;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;
use App\Domain\Telemetry\ProviderUsage;
use PHPUnit\Framework\TestCase;

final class TelemetryHandlersTest extends TestCase
{
    private InMemoryPipelineTelemetryRepository $repository;

    private WorkspaceAnalyticsAggregator $aggregator;

    protected function setUp(): void
    {
        $this->repository = new InMemoryPipelineTelemetryRepository();
        $this->aggregator = new WorkspaceAnalyticsAggregator();
    }

    public function testCollectPipelineMetricsAppendsRecord(): void
    {
        $telemetry = $this->sampleTelemetry(true, 120.0, 92);

        (new CollectPipelineMetricsHandler($this->repository))(
            new CollectPipelineMetricsCommand($telemetry),
        );

        self::assertCount(1, $this->repository->findByWorkspaceId($this->workspaceId()));
    }

    public function testGetWorkspaceAnalyticsAggregatesAverages(): void
    {
        $this->repository->append($this->sampleTelemetry(true, 100.0, 90));
        $this->repository->append($this->sampleTelemetry(true, 200.0, 98));

        $result = (new GetWorkspaceAnalyticsHandler($this->repository, $this->aggregator))(
            new GetWorkspaceAnalyticsQuery($this->workspaceId()),
        );

        self::assertSame(2, $result->processedVideos);
        self::assertSame(150.0, $result->averageProcessingTimeSeconds);
        self::assertSame('2m 30s', $result->averageProcessingTimeLabel);
        self::assertSame(94, $result->averageQuality);
        self::assertSame(100.0, $result->successRate);
        self::assertSame('ollama', $result->topTranslationProvider);
        self::assertSame('f5_tts', $result->topTtsProvider);
    }

    public function testGetProviderStatisticsReturnsDeterministicRanking(): void
    {
        $this->repository->append($this->sampleTelemetry(true, 100.0, 90));
        $this->repository->append($this->sampleTelemetry(true, 120.0, 88));

        $result = (new GetProviderStatisticsHandler($this->repository, $this->aggregator))(
            new GetProviderStatisticsQuery($this->workspaceId()),
        );

        self::assertCount(2, $result->providers);
        self::assertSame('translation', $result->providers[0]->stage);
        self::assertSame(2, $result->providers[0]->invocationCount);
    }

    public function testEmptyWorkspaceReturnsZeroedAnalytics(): void
    {
        $result = (new GetWorkspaceAnalyticsHandler($this->repository, $this->aggregator))(
            new GetWorkspaceAnalyticsQuery($this->workspaceId()),
        );

        self::assertSame(0, $result->processedVideos);
        self::assertSame(0.0, $result->averageProcessingTimeSeconds);
        self::assertSame('0s', $result->averageProcessingTimeLabel);
        self::assertSame(0, $result->averageQuality);
        self::assertSame(0.0, $result->successRate);
        self::assertSame([], $result->recentErrors);
    }

    private function workspaceId(): string
    {
        return '550e8400-e29b-41d4-a716-446655490001';
    }

    private function sampleTelemetry(bool $success, float $processingSeconds, int $quality): PipelineTelemetry
    {
        return PipelineTelemetry::create(
            PipelineTelemetryId::generate(),
            $this->workspaceId(),
            '550e8400-e29b-41d4-a716-446655490010',
            $success,
            [
                ExecutionMetric::of(ExecutionMetricType::ProcessingTime, $processingSeconds),
                ExecutionMetric::of(ExecutionMetricType::GpuUsage, 71.0),
            ],
            [
                ProviderUsage::create('translation', 'ollama', 1, 45.0),
                ProviderUsage::create('text_to_speech', 'f5_tts', 1, 30.0),
            ],
            qualityScore: $quality,
            errorMessage: $success ? null : 'Translation retry failed',
        );
    }
}

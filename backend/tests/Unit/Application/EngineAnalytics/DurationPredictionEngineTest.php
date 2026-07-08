<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\EngineAnalytics;

use App\Application\EngineAnalytics\DurationPredictionEngine;
use App\Application\Pipeline\Estimation\HardwareAwareEstimateResolver;
use App\Application\Pipeline\Estimation\MediaDurationResolver;
use App\Application\Pipeline\Estimation\PipelineStageDurationEstimator;
use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Runtime\RuntimePlatformInterface;
use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DurationPredictionEngineTest extends TestCase
{
    public function testUsesHistoricalMedianAfterEnoughSamples(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $repository = new InMemoryEngineExecutionHistoryRepository();
        $runtime = $this->createStub(RuntimePlatformInterface::class);
        $runtime->method('hardwareProfile')->willReturn(['profile' => ['type' => 'low_end_local']]);
        $engine = $this->createEngine($repository, $runtime);

        foreach ([300, 360, 420] as $seconds) {
            $repository->record($this->sampleExecution($seconds));
        }

        $estimate = $engine->estimateForStage($videoId, PipelineStageType::Translation, 'ollama');

        self::assertSame('historical', $estimate['source']);
        self::assertSame(360, $estimate['maxSeconds']);
        self::assertGreaterThan(0.5, $estimate['confidence']);
    }

    public function testFallsBackToRulesWhenHistoryIsSparse(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $repository = new InMemoryEngineExecutionHistoryRepository();
        $runtime = $this->createStub(RuntimePlatformInterface::class);
        $runtime->method('hardwareProfile')->willReturn(['profile' => ['type' => 'low_end_local']]);
        $engine = $this->createEngine($repository, $runtime);

        $estimate = $engine->estimateForStage($videoId, PipelineStageType::Translation, 'ollama');

        self::assertSame('rules', $estimate['source']);
        self::assertGreaterThan(0, $estimate['maxSeconds']);
    }

    private function createEngine(
        InMemoryEngineExecutionHistoryRepository $repository,
        RuntimePlatformInterface $runtime,
    ): DurationPredictionEngine {
        $videoRepository = $this->createStub(VideoRepositoryInterface::class);
        $fallback = new PipelineStageDurationEstimator(
            new TranscriptionDurationEstimator(
                new MediaDurationResolver($videoRepository),
                new HardwareAwareEstimateResolver(false),
                'large-v3',
            ),
            new MediaDurationResolver($videoRepository),
        );

        return new DurationPredictionEngine($repository, $fallback, $runtime);
    }

    private function sampleExecution(int $seconds): EngineExecutionHistory
    {
        $startedAt = new DateTimeImmutable('2026-07-08 09:00:00');
        $completedAt = $startedAt->modify(sprintf('+%d seconds', $seconds));

        return EngineExecutionHistory::fromPipelineExecution(
            PipelineJobId::generate(),
            '550e8400-e29b-41d4-a716-446655440099',
            PipelineStageType::Translation,
            'ollama',
            'low_end_local',
            EngineExecutionStatus::Completed,
            $startedAt,
            $completedAt,
            600,
        );
    }
}

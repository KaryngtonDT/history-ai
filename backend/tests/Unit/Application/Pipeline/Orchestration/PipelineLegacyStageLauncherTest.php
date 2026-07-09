<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Orchestration;

use App\Application\EngineAnalytics\DurationPredictionEngine;
use App\Application\EngineAnalytics\EngineExecutionRecorder;
use App\Application\EngineAnalytics\EngineStatisticsAggregator;
use App\Application\EngineAnalytics\PipelineJobAnalyticsEnricher;
use App\Application\Pipeline\Estimation\HardwareAwareEstimateResolver;
use App\Application\Pipeline\Estimation\MediaDurationResolver;
use App\Application\Pipeline\Estimation\PipelineStageDurationEstimator;
use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Pipeline\Orchestration\PipelineDependencyResolver;
use App\Application\Pipeline\Orchestration\PipelineInvalidationService;
use App\Application\Pipeline\Orchestration\PipelineJobLiveViewService;
use App\Application\Pipeline\Orchestration\PipelineLegacyStageLauncher;
use App\Application\Pipeline\Orchestration\PipelineNotificationService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Application\Pipeline\Orchestration\PipelineProgressService;
use App\Application\Runtime\RuntimePlatformInterface;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineNotificationRepositoryInterface;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\Video\VideoRepositoryInterface;
use App\Tests\Unit\Application\EngineAnalytics\InMemoryEngineExecutionHistoryRepository;
use PHPUnit\Framework\TestCase;

final class PipelineLegacyStageLauncherTest extends TestCase
{
    public function testRejectsDuplicateActiveTranslationJob(): void
    {
        $active = PipelineJob::reconstitute(
            PipelineJobId::generate(),
            'source-1',
            'source-1',
            null,
            'source-1',
            PipelineSourceType::Video,
            PipelineStageType::Translation,
            PipelineJobStatus::Running,
            10,
            'translating',
            null,
            null,
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
            600,
            540,
            60,
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            false,
            [],
        );

        $repository = $this->createStub(PipelineJobRepositoryInterface::class);
        $repository->method('findActiveBySourceAndStage')->willReturn($active);

        $launcher = new PipelineLegacyStageLauncher(
            $this->createOrchestrator($repository),
            $repository,
            new PipelineInvalidationService(
                $repository,
                new PipelineDependencyResolver(),
                new PipelineNotificationService(
                    $this->createStub(PipelineNotificationRepositoryInterface::class),
                ),
            ),
        );

        $this->expectException(\App\Domain\PipelineJob\Exception\InvalidPipelineJobException::class);
        $launcher->launch('source-1', PipelineStageType::Translation, [
            'targetLanguages' => ['french'],
        ]);
    }

    private function createOrchestrator(PipelineJobRepositoryInterface $repository): PipelineOrchestrator
    {
        $videoRepository = $this->createStub(VideoRepositoryInterface::class);
        $runtimePlatform = $this->createStub(RuntimePlatformInterface::class);
        $historyRepository = new InMemoryEngineExecutionHistoryRepository();
        $fallbackEstimator = new PipelineStageDurationEstimator(
            new TranscriptionDurationEstimator(
                new MediaDurationResolver($videoRepository),
                new HardwareAwareEstimateResolver(false),
                'large-v3',
            ),
            new MediaDurationResolver($videoRepository),
        );
        $hardwareRepository = $this->createStub(\App\Domain\Hardware\HardwareRepositoryInterface::class);
        $durationPredictionEngine = new DurationPredictionEngine(
            $historyRepository,
            $fallbackEstimator,
            $hardwareRepository,
        );

        return new PipelineOrchestrator(
            $repository,
            new PipelineDependencyResolver(),
            new PipelineInvalidationService(
                $repository,
                new PipelineDependencyResolver(),
                new PipelineNotificationService(
                    $this->createStub(PipelineNotificationRepositoryInterface::class),
                ),
            ),
            new PipelineNotificationService(
                $this->createStub(PipelineNotificationRepositoryInterface::class),
            ),
            new PipelineProgressService($repository),
            new PipelineJobLiveViewService(),
            $durationPredictionEngine,
            new EngineExecutionRecorder($historyRepository, $runtimePlatform, new MediaDurationResolver($videoRepository)),
            new EngineStatisticsAggregator($historyRepository, $durationPredictionEngine),
            new PipelineJobAnalyticsEnricher($historyRepository, $runtimePlatform),
            $this->createStub(VideoProcessingQueueInterface::class),
            $videoRepository,
        );
    }
}

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
use App\Application\Runtime\RuntimePlatformInterface;
use App\Tests\Unit\Application\EngineAnalytics\InMemoryEngineExecutionHistoryRepository;
use App\Application\Pipeline\Orchestration\PipelineDependencyResolver;
use App\Application\Pipeline\Orchestration\PipelineInvalidationService;
use App\Application\Pipeline\Orchestration\PipelineNotificationService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Application\Pipeline\Orchestration\PipelineProgressService;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\Hardware\HardwareCapability;
use App\Domain\Hardware\HardwareDetectionReport;
use App\Domain\Hardware\HardwareProfile;
use App\Domain\Hardware\HardwareProfileType;
use App\Domain\Hardware\HardwareRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineNotificationRepositoryInterface;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class PipelineOrchestratorTest extends TestCase
{
    public function testBuildSourceStatusTreatsQueuedUserChoiceJobsAsWaitingForChoice(): void
    {
        $sourceId = '550e8400-e29b-41d4-a716-446655440099';
        $queuedChoiceJob = PipelineJob::reconstitute(
            PipelineJobId::generate(),
            $sourceId,
            $sourceId,
            null,
            $sourceId,
            PipelineSourceType::Youtube,
            PipelineStageType::SpeechToText,
            PipelineJobStatus::Queued,
            0,
            null,
            'large-v3',
            'faster_whisper',
            new \DateTimeImmutable(),
            new \DateTimeImmutable(),
            null,
            null,
            7200,
            7200,
            0,
            null,
            null,
            null,
            null,
            [],
            [],
            null,
            true,
            ['youtube_transcript', 'local_engine'],
        );

        $repository = $this->createStub(PipelineJobRepositoryInterface::class);
        $repository->method('findBySourceId')->willReturn([$queuedChoiceJob]);

        $orchestrator = $this->createOrchestrator($repository);
        $status = $orchestrator->buildSourceStatus($sourceId);

        self::assertCount(1, $status['jobsWaitingUserChoice']);
        self::assertSame([], $status['activeJobs']);
        self::assertTrue($status['requiresUserAction']);
    }

    public function testBuildSourceStatusKeepsRunningJobsActive(): void
    {
        $sourceId = '550e8400-e29b-41d4-a716-446655440099';
        $runningJob = PipelineJob::createQueued(
            PipelineJobId::generate(),
            $sourceId,
            PipelineSourceType::Video,
            PipelineStageType::SpeechToText,
            $sourceId,
        )->start('local_stt');

        $repository = $this->createStub(PipelineJobRepositoryInterface::class);
        $repository->method('findBySourceId')->willReturn([$runningJob]);

        $orchestrator = $this->createOrchestrator($repository);
        $status = $orchestrator->buildSourceStatus($sourceId);

        self::assertCount(1, $status['activeJobs']);
        self::assertSame([], $status['jobsWaitingUserChoice']);
    }

    public function testContinueToNextStageStartsAndEnqueuesTranslation(): void
    {
        $sourceId = '550e8400-e29b-41d4-a716-446655440099';
        $sttJob = PipelineJob::createQueued(
            PipelineJobId::generate(),
            $sourceId,
            PipelineSourceType::Video,
            PipelineStageType::SpeechToText,
            $sourceId,
        )
            ->start('processing')
            ->complete('transcript-artifact');

        $repository = new InMemoryPipelineJobRepository($sttJob);

        $queue = $this->createMock(VideoProcessingQueueInterface::class);
        $queue->expects(self::once())
            ->method('enqueue')
            ->with(
                self::callback(static fn (VideoId $videoId): bool => $sourceId === $videoId->value),
                ProcessingMode::Manual,
                null,
                null,
                PipelineStageType::Translation,
                self::isString(),
            );

        $orchestrator = $this->createOrchestrator($repository, $queue);
        $started = $orchestrator->continueToNextStage($sttJob->jobId());

        self::assertNotNull($started);
        self::assertSame(PipelineStageType::Translation, $started->stage());
        self::assertSame(PipelineJobStatus::Running, $started->status());
        self::assertNotNull($started->estimatedDurationSeconds());
        self::assertNotNull($started->startedAt());
    }

    private function createOrchestrator(
        PipelineJobRepositoryInterface $repository,
        ?VideoProcessingQueueInterface $queue = null,
    ): PipelineOrchestrator {
        $notificationRepository = $this->createStub(PipelineNotificationRepositoryInterface::class);
        $notificationService = new PipelineNotificationService($notificationRepository);
        $dependencyResolver = new PipelineDependencyResolver();
        $invalidationService = new PipelineInvalidationService(
            $repository,
            $dependencyResolver,
            $notificationService,
        );

        $videoRepository = $this->createStub(VideoRepositoryInterface::class);
        $runtimePlatform = $this->createStub(RuntimePlatformInterface::class);
        $runtimePlatform->method('hardwareProfile')->willReturn(['profile' => ['type' => 'low_end_local']]);
        $historyRepository = new InMemoryEngineExecutionHistoryRepository();
        $fallbackEstimator = new PipelineStageDurationEstimator(
            new TranscriptionDurationEstimator(
                new MediaDurationResolver($videoRepository),
                new HardwareAwareEstimateResolver(false),
                'large-v3',
            ),
            new MediaDurationResolver($videoRepository),
        );
        $hardwareRepository = $this->createHardwareRepository();
        $durationPredictionEngine = new DurationPredictionEngine(
            $historyRepository,
            $fallbackEstimator,
            $hardwareRepository,
        );
        $executionRecorder = new EngineExecutionRecorder(
            $historyRepository,
            $runtimePlatform,
            new MediaDurationResolver($videoRepository),
        );
        $statisticsAggregator = new EngineStatisticsAggregator($historyRepository, $durationPredictionEngine);
        $jobAnalyticsEnricher = new PipelineJobAnalyticsEnricher($historyRepository, $runtimePlatform);

        return new PipelineOrchestrator(
            $repository,
            $dependencyResolver,
            $invalidationService,
            $notificationService,
            new PipelineProgressService($repository),
            $durationPredictionEngine,
            $executionRecorder,
            $statisticsAggregator,
            $jobAnalyticsEnricher,
            $queue ?? $this->createStub(VideoProcessingQueueInterface::class),
            $videoRepository,
        );
    }

    private function createHardwareRepository(): HardwareRepositoryInterface
    {
        $capabilities = new HardwareCapability();
        $report = new HardwareDetectionReport(
            new HardwareProfile(HardwareProfileType::LowEndLocal, $capabilities, 'Low end local'),
            $capabilities,
            new \DateTimeImmutable(),
        );
        $hardware = $this->createStub(HardwareRepositoryInterface::class);
        $hardware->method('detect')->willReturn($report);

        return $hardware;
    }
}

final class InMemoryPipelineJobRepository implements PipelineJobRepositoryInterface
{
    /** @var array<string, PipelineJob> */
    private array $jobs = [];

    public function __construct(PipelineJob ...$seedJobs)
    {
        foreach ($seedJobs as $job) {
            $this->jobs[$job->jobId()->value] = $job;
        }
    }

    public function save(PipelineJob $job): void
    {
        $this->jobs[$job->jobId()->value] = $job;
    }

    public function findById(PipelineJobId $jobId): ?PipelineJob
    {
        return $this->jobs[$jobId->value] ?? null;
    }

    public function findActiveBySourceAndStage(string $sourceId, PipelineStageType $stage): ?PipelineJob
    {
        foreach ($this->jobs as $job) {
            if ($job->sourceId() !== $sourceId) {
                continue;
            }

            if ($job->stage() !== $stage) {
                continue;
            }

            if ($job->status()->isActive() || $job->status()->isWaitingForUser()) {
                return $job;
            }
        }

        return null;
    }

    public function findBySourceId(string $sourceId): array
    {
        return array_values(array_filter(
            $this->jobs,
            static fn (PipelineJob $job): bool => $job->sourceId() === $sourceId,
        ));
    }

    public function findActiveBySourceId(string $sourceId): array
    {
        return array_values(array_filter(
            $this->jobs,
            static fn (PipelineJob $job): bool => $job->sourceId() === $sourceId
                && ($job->status()->isActive() || $job->status()->isWaitingForUser()),
        ));
    }
}

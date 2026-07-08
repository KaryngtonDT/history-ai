<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Orchestration;

use App\Application\Pipeline\Estimation\HardwareAwareEstimateResolver;
use App\Application\Pipeline\Estimation\MediaDurationResolver;
use App\Application\Pipeline\Estimation\PipelineStageDurationEstimator;
use App\Application\Pipeline\Estimation\TranscriptionDurationEstimator;
use App\Application\Pipeline\Orchestration\PipelineDependencyResolver;
use App\Application\Pipeline\Orchestration\PipelineInvalidationService;
use App\Application\Pipeline\Orchestration\PipelineNotificationService;
use App\Application\Pipeline\Orchestration\PipelineOrchestrator;
use App\Application\Pipeline\Orchestration\PipelineProgressService;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
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

        /** @var array<string, PipelineJob> $jobs */
        $jobs = [$sttJob->jobId()->value => $sttJob];

        $repository = $this->createMock(PipelineJobRepositoryInterface::class);
        $repository->expects(self::any())
            ->method('findById')
            ->willReturnCallback(
                static fn (PipelineJobId $jobId): ?PipelineJob => $jobs[$jobId->value] ?? null,
            );
        $repository->expects(self::any())
            ->method('save')
            ->willReturnCallback(
                static function (PipelineJob $job) use (&$jobs): void {
                    $jobs[$job->jobId()->value] = $job;
                },
            );
        $repository->expects(self::any())
            ->method('findActiveBySourceAndStage')
            ->willReturnCallback(
                static function (string $source, PipelineStageType $stage) use (&$jobs): ?PipelineJob {
                    foreach ($jobs as $job) {
                        if ($job->sourceId() !== $source) {
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
                },
            );

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
        $stageDurationEstimator = new PipelineStageDurationEstimator(
            new TranscriptionDurationEstimator(
                new MediaDurationResolver($videoRepository),
                new HardwareAwareEstimateResolver(false),
                'large-v3',
            ),
            new MediaDurationResolver($videoRepository),
        );

        return new PipelineOrchestrator(
            $repository,
            $dependencyResolver,
            $invalidationService,
            $notificationService,
            new PipelineProgressService($repository),
            $stageDurationEstimator,
            $queue ?? $this->createStub(VideoProcessingQueueInterface::class),
            $videoRepository,
        );
    }
}

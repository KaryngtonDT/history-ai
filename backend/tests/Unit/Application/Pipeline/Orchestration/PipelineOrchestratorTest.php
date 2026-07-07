<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Pipeline\Orchestration;

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
use App\Domain\PipelineJob\PipelineSourceType;
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

    private function createOrchestrator(PipelineJobRepositoryInterface $repository): PipelineOrchestrator
    {
        $notificationService = $this->createStub(PipelineNotificationService::class);
        $dependencyResolver = new PipelineDependencyResolver();
        $invalidationService = new PipelineInvalidationService(
            $repository,
            $dependencyResolver,
            $notificationService,
        );

        return new PipelineOrchestrator(
            $repository,
            $dependencyResolver,
            $invalidationService,
            $notificationService,
            new PipelineProgressService($repository),
            $this->createStub(TranscriptionDurationEstimator::class),
            $this->createStub(VideoProcessingQueueInterface::class),
            $this->createStub(VideoRepositoryInterface::class),
        );
    }
}

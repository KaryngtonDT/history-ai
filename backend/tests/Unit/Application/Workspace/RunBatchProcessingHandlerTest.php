<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Workspace;

use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Application\Workspace\Commands\RunBatchProcessingCommand;
use App\Application\Workspace\RunBatchProcessingHandler;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobRepositoryInterface;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use App\Domain\Workspace\ProjectVideo;
use PHPUnit\Framework\TestCase;

final class RunBatchProcessingHandlerTest extends TestCase
{
    public function testProcessesAllProjectVideos(): void
    {
        $project = $this->sampleProject();
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturnCallback(
            fn (VideoId $videoId): VideoJob => VideoJob::createUploaded(
                $videoId,
                'clip.mp4',
                VideoLanguage::English,
            ),
        );

        $queue = $this->createMock(VideoProcessingQueueInterface::class);
        $queue->expects(self::exactly(2))->method('enqueue');

        $handler = new RunBatchProcessingHandler(
            $this->projectRepository($project),
            $this->batchJobRepository(),
            $videoRepository,
            $queue,
        );

        $result = $handler(new RunBatchProcessingCommand(
            $project->id()->value,
            ['fr', 'de'],
            ProcessingMode::Automatic,
        ));

        self::assertSame(2, $result->totalVideos);
        self::assertSame(2, $result->queuedVideos);
        self::assertSame('running', $result->status);
        self::assertSame(0, $result->progress);
    }

    public function testPartialFailureDoesNotStopBatch(): void
    {
        $project = $this->sampleProject();
        $missingId = new VideoId('550e8400-e29b-41d4-a716-446655440011');

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturnCallback(
            fn (VideoId $videoId): ?VideoJob => $videoId->equals($missingId)
                ? null
                : VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English),
        );

        $queue = $this->createMock(VideoProcessingQueueInterface::class);
        $queue->expects(self::once())->method('enqueue');

        $handler = new RunBatchProcessingHandler(
            $this->projectRepository($project),
            $this->batchJobRepository(),
            $videoRepository,
            $queue,
        );

        $result = $handler(new RunBatchProcessingCommand($project->id()->value, ['fr']));

        self::assertSame(1, $result->queuedVideos);
        self::assertSame(['550e8400-e29b-41d4-a716-446655440011'], $result->failedVideoIds);
        self::assertSame('running', $result->status);
    }

    public function testAllDispatchFailuresMarkBatchFailed(): void
    {
        $project = $this->sampleProject();
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $queue = $this->createMock(VideoProcessingQueueInterface::class);
        $queue->expects(self::never())->method('enqueue');

        $handler = new RunBatchProcessingHandler(
            $this->projectRepository($project),
            $this->batchJobRepository(),
            $videoRepository,
            $queue,
        );

        $result = $handler(new RunBatchProcessingCommand($project->id()->value, ['fr']));

        self::assertSame('failed', $result->status);
        self::assertSame(0, $result->queuedVideos);
    }

    public function testEmptyProjectThrows(): void
    {
        $project = Project::create(ProjectId::generate(), 'Empty');
        $handler = new RunBatchProcessingHandler(
            $this->projectRepository($project),
            $this->batchJobRepository(),
            $this->createMock(VideoRepositoryInterface::class),
            $this->createMock(VideoProcessingQueueInterface::class),
        );

        $this->expectException(InvalidProjectException::class);

        $handler(new RunBatchProcessingCommand($project->id()->value, ['fr']));
    }

    private function sampleProject(): Project
    {
        return Project::create(ProjectId::generate(), 'Campaign')
            ->addVideo(ProjectVideo::create(
                new VideoId('550e8400-e29b-41d4-a716-446655440010'),
                'Interview.mp4',
            ))
            ->addVideo(ProjectVideo::create(
                new VideoId('550e8400-e29b-41d4-a716-446655440011'),
                'Podcast.mp4',
            ));
    }

    private function projectRepository(Project $project): ProjectRepositoryInterface
    {
        $repository = $this->createMock(ProjectRepositoryInterface::class);
        $repository->method('findById')->willReturn($project);

        return $repository;
    }

    private function batchJobRepository(): BatchJobRepositoryInterface
    {
        $saved = null;
        $repository = $this->createMock(BatchJobRepositoryInterface::class);
        $repository->method('save')->willReturnCallback(function (BatchJob $batchJob) use (&$saved): void {
            $saved = $batchJob;
        });
        $repository->method('findById')->willReturnCallback(
            static fn (): ?BatchJob => $saved,
        );

        return $repository;
    }
}

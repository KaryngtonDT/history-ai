<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Processing;

use App\Application\Processing\Commands\CompleteProcessingJobCommand;
use App\Application\Processing\Commands\StartProcessingJobCommand;
use App\Application\Processing\Commands\UpdateProcessingProgressCommand;
use App\Application\Processing\Handlers\CompleteProcessingJobHandler;
use App\Application\Processing\Handlers\StartProcessingJobHandler;
use App\Application\Processing\Handlers\UpdateProcessingProgressHandler;
use App\Domain\Content\ContentId;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\Exception\ProcessingJobNotFoundException;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobProgress;
use App\Domain\Processing\ProcessingJobRepositoryInterface;
use App\Domain\Processing\ProcessingJobStatus;
use App\Domain\Processing\ProcessingJobType;
use PHPUnit\Framework\TestCase;

final class ProcessingJobLifecycleHandlersTest extends TestCase
{
    public function testStartTransitionsPendingJobToRunning(): void
    {
        $job = $this->createPendingJob();
        $repository = $this->createRepository($job);

        $handler = new StartProcessingJobHandler($repository);
        $handler(new StartProcessingJobCommand($job->id()->value));

        self::assertSame(ProcessingJobStatus::Running, $job->status());
    }

    public function testUpdateProgressIncrementsRunningJob(): void
    {
        $job = $this->createRunningJob();
        $repository = $this->createRepository($job);

        $handler = new UpdateProcessingProgressHandler($repository);
        $handler(new UpdateProcessingProgressCommand($job->id()->value, 42));

        self::assertSame(42, $job->progress()->percentage());
    }

    public function testCompleteMarksJobAsCompleted(): void
    {
        $job = $this->createRunningJob();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(80));
        $repository = $this->createRepository($job);

        $handler = new CompleteProcessingJobHandler($repository);
        $handler(new CompleteProcessingJobCommand($job->id()->value));

        self::assertSame(ProcessingJobStatus::Completed, $job->status());
        self::assertSame(100, $job->progress()->percentage());
    }

    public function testStartThrowsWhenJobNotFound(): void
    {
        $repository = $this->createStub(ProcessingJobRepositoryInterface::class);
        $repository->method('findById')->willReturn(null);

        $handler = new StartProcessingJobHandler($repository);

        $this->expectException(ProcessingJobNotFoundException::class);
        $handler(new StartProcessingJobCommand(ProcessingJobId::generate()->value));
    }

    public function testStartRejectsNonPendingJob(): void
    {
        $job = $this->createRunningJob();
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository->method('findById')->willReturn($job);
        $repository->expects(self::never())->method('save');

        $handler = new StartProcessingJobHandler($repository);

        $this->expectException(InvalidProcessingJobException::class);
        $handler(new StartProcessingJobCommand($job->id()->value));
    }

    private function createPendingJob(): ProcessingJob
    {
        return ProcessingJob::create(
            ProcessingJobId::generate(),
            ContentId::generate(),
            ProcessingJobType::Summary,
        );
    }

    private function createRunningJob(): ProcessingJob
    {
        $job = $this->createPendingJob();
        $job->start();

        return $job;
    }

    private function createRepository(ProcessingJob $job): ProcessingJobRepositoryInterface
    {
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository->method('findById')->willReturn($job);
        $repository->expects(self::once())->method('save')->with($job);

        return $repository;
    }
}

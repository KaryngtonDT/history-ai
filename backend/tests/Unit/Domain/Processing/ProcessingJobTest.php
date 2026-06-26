<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Processing;

use App\Domain\Content\ContentId;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobProgress;
use App\Domain\Processing\ProcessingJobStatus;
use App\Domain\Processing\ProcessingJobType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProcessingJobTest extends TestCase
{
    public function testCreateStartsInPendingWithZeroProgress(): void
    {
        $contentId = ContentId::generate();
        $job = ProcessingJob::create(
            ProcessingJobId::generate(),
            $contentId,
            ProcessingJobType::Summary,
        );

        self::assertSame(ProcessingJobStatus::Pending, $job->status());
        self::assertSame(ProcessingJobType::Summary, $job->type());
        self::assertTrue($job->contentId()->equals($contentId));
        self::assertSame(0, $job->progress()->percentage());
        self::assertFalse($job->progress()->isComplete());
        self::assertNull($job->startedAt());
        self::assertNull($job->completedAt());
        self::assertNull($job->failedAt());
        self::assertNotEmpty($job->id()->value);
        self::assertLessThanOrEqual($job->createdAt(), $job->updatedAt());
    }

    public function testMultipleJobsCanExistForSameContentWithDifferentTypes(): void
    {
        $contentId = ContentId::generate();

        $summary = ProcessingJob::create(
            ProcessingJobId::generate(),
            $contentId,
            ProcessingJobType::Summary,
        );
        $quiz = ProcessingJob::create(
            ProcessingJobId::generate(),
            $contentId,
            ProcessingJobType::Quiz,
        );
        $podcast = ProcessingJob::create(
            ProcessingJobId::generate(),
            $contentId,
            ProcessingJobType::Podcast,
        );

        self::assertTrue($summary->contentId()->equals($contentId));
        self::assertTrue($quiz->contentId()->equals($contentId));
        self::assertTrue($podcast->contentId()->equals($contentId));
        self::assertSame(ProcessingJobType::Summary, $summary->type());
        self::assertSame(ProcessingJobType::Quiz, $quiz->type());
        self::assertSame(ProcessingJobType::Podcast, $podcast->type());
        self::assertFalse($summary->id()->equals($quiz->id()));
    }

    public function testStartTransitionsPendingToRunningAndRecordsStartedAt(): void
    {
        $job = $this->createPendingJob();

        $job->start();

        self::assertSame(ProcessingJobStatus::Running, $job->status());
        self::assertSame(0, $job->progress()->percentage());
        self::assertNotNull($job->startedAt());
    }

    public function testUpdateProgressWhileRunning(): void
    {
        $job = $this->createRunningJob();

        $job->updateProgress(ProcessingJobProgress::fromPercentage(15));
        self::assertSame(15, $job->progress()->percentage());

        $job->updateProgress(ProcessingJobProgress::fromPercentage(42));
        self::assertSame(42, $job->progress()->percentage());
    }

    public function testCompleteSetsProgressToOneHundredAndRecordsCompletedAt(): void
    {
        $job = $this->createRunningJob();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(88));

        $job->complete();

        self::assertSame(ProcessingJobStatus::Completed, $job->status());
        self::assertSame(100, $job->progress()->percentage());
        self::assertTrue($job->progress()->isComplete());
        self::assertNotNull($job->completedAt());
    }

    public function testFailRecordsFailedAt(): void
    {
        $job = $this->createRunningJob();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(39));

        $job->fail();

        self::assertSame(ProcessingJobStatus::Failed, $job->status());
        self::assertSame(39, $job->progress()->percentage());
        self::assertNotNull($job->failedAt());
    }

    public function testCancelKeepsZeroProgress(): void
    {
        $job = $this->createPendingJob();

        $job->cancel();

        self::assertSame(ProcessingJobStatus::Cancelled, $job->status());
        self::assertSame(0, $job->progress()->percentage());
    }

    public function testWorkerHappyPathWithIncrementalProgress(): void
    {
        $job = $this->createPendingJob();

        $job->start();
        foreach ([5, 17, 39, 61, 88] as $step) {
            $job->updateProgress(ProcessingJobProgress::fromPercentage($step));
        }
        $job->complete();

        self::assertSame(ProcessingJobStatus::Completed, $job->status());
        self::assertSame(100, $job->progress()->percentage());
        self::assertNotNull($job->startedAt());
        self::assertNotNull($job->completedAt());
    }

    #[DataProvider('invalidProgressPercentageProvider')]
    public function testProcessingJobProgressRejectsOutOfRangeValues(int $value): void
    {
        $this->expectException(InvalidProcessingJobException::class);

        ProcessingJobProgress::fromPercentage($value);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function invalidProgressPercentageProvider(): iterable
    {
        yield 'negative' => [-1];
        yield 'over 100' => [101];
        yield 'far over 100' => [130];
    }

    public function testDecreasingProgressIsRejected(): void
    {
        $job = $this->createRunningJob();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(42));

        $this->expectException(InvalidProcessingJobException::class);

        $job->updateProgress(ProcessingJobProgress::fromPercentage(15));
    }

    public function testSameProgressIsRejected(): void
    {
        $job = $this->createRunningJob();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(42));

        $this->expectException(InvalidProcessingJobException::class);

        $job->updateProgress(ProcessingJobProgress::fromPercentage(42));
    }

    public function testUpdateProgressToOneHundredIsRejected(): void
    {
        $job = $this->createRunningJob();

        $this->expectException(InvalidProcessingJobException::class);

        $job->updateProgress(ProcessingJobProgress::fromPercentage(100));
    }

    public function testUpdateProgressToZeroWhileRunningIsRejected(): void
    {
        $job = $this->createRunningJob();
        $job->updateProgress(ProcessingJobProgress::fromPercentage(10));

        $this->expectException(InvalidProcessingJobException::class);

        $job->updateProgress(ProcessingJobProgress::fromPercentage(0));
    }

    #[DataProvider('terminalStatusProgressUpdateProvider')]
    public function testUpdateProgressAfterTerminalStatusIsRejected(ProcessingJobStatus $status): void
    {
        $job = $this->createJobWithStatus($status);

        $this->expectException(InvalidProcessingJobException::class);

        $job->updateProgress(ProcessingJobProgress::fromPercentage(50));
    }

    /**
     * @return iterable<string, array{ProcessingJobStatus}>
     */
    public static function terminalStatusProgressUpdateProvider(): iterable
    {
        yield 'completed' => [ProcessingJobStatus::Completed];
        yield 'failed' => [ProcessingJobStatus::Failed];
        yield 'cancelled' => [ProcessingJobStatus::Cancelled];
    }

    public function testUpdateProgressWhilePendingIsRejected(): void
    {
        $job = $this->createPendingJob();

        $this->expectException(InvalidProcessingJobException::class);

        $job->updateProgress(ProcessingJobProgress::fromPercentage(50));
    }

    #[DataProvider('invalidStartProvider')]
    public function testStartRejectsInvalidStatuses(ProcessingJobStatus $status): void
    {
        $job = $this->createJobWithStatus($status);

        $this->expectException(InvalidProcessingJobException::class);

        $job->start();
    }

    /**
     * @return iterable<string, array{ProcessingJobStatus}>
     */
    public static function invalidStartProvider(): iterable
    {
        yield 'running' => [ProcessingJobStatus::Running];
        yield 'completed' => [ProcessingJobStatus::Completed];
        yield 'failed' => [ProcessingJobStatus::Failed];
        yield 'cancelled' => [ProcessingJobStatus::Cancelled];
    }

    #[DataProvider('invalidCompleteProvider')]
    public function testCompleteRejectsInvalidStatuses(ProcessingJobStatus $status): void
    {
        $job = $this->createJobWithStatus($status);

        $this->expectException(InvalidProcessingJobException::class);

        $job->complete();
    }

    /**
     * @return iterable<string, array{ProcessingJobStatus}>
     */
    public static function invalidCompleteProvider(): iterable
    {
        yield 'pending' => [ProcessingJobStatus::Pending];
        yield 'completed' => [ProcessingJobStatus::Completed];
        yield 'failed' => [ProcessingJobStatus::Failed];
        yield 'cancelled' => [ProcessingJobStatus::Cancelled];
    }

    #[DataProvider('invalidFailProvider')]
    public function testFailRejectsInvalidStatuses(ProcessingJobStatus $status): void
    {
        $job = $this->createJobWithStatus($status);

        $this->expectException(InvalidProcessingJobException::class);

        $job->fail();
    }

    /**
     * @return iterable<string, array{ProcessingJobStatus}>
     */
    public static function invalidFailProvider(): iterable
    {
        yield 'pending' => [ProcessingJobStatus::Pending];
        yield 'completed' => [ProcessingJobStatus::Completed];
        yield 'failed' => [ProcessingJobStatus::Failed];
        yield 'cancelled' => [ProcessingJobStatus::Cancelled];
    }

    #[DataProvider('invalidCancelProvider')]
    public function testCancelRejectsInvalidStatuses(ProcessingJobStatus $status): void
    {
        $job = $this->createJobWithStatus($status);

        $this->expectException(InvalidProcessingJobException::class);

        $job->cancel();
    }

    /**
     * @return iterable<string, array{ProcessingJobStatus}>
     */
    public static function invalidCancelProvider(): iterable
    {
        yield 'running' => [ProcessingJobStatus::Running];
        yield 'completed' => [ProcessingJobStatus::Completed];
        yield 'failed' => [ProcessingJobStatus::Failed];
        yield 'cancelled' => [ProcessingJobStatus::Cancelled];
    }

    public function testInvalidProcessingJobIdIsRejected(): void
    {
        $this->expectException(InvalidProcessingJobException::class);

        new ProcessingJobId('not-a-uuid');
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

    private function createJobWithStatus(ProcessingJobStatus $status): ProcessingJob
    {
        $createdAt = new \DateTimeImmutable('2026-06-26 10:00:00');
        $updatedAt = new \DateTimeImmutable('2026-06-26 10:00:00');

        $progress = match ($status) {
            ProcessingJobStatus::Completed => ProcessingJobProgress::complete(),
            default => ProcessingJobProgress::zero(),
        };

        $startedAt = ProcessingJobStatus::Pending === $status || ProcessingJobStatus::Cancelled === $status
            ? null
            : $createdAt;

        $completedAt = ProcessingJobStatus::Completed === $status ? $updatedAt : null;
        $failedAt = ProcessingJobStatus::Failed === $status ? $updatedAt : null;

        return ProcessingJob::reconstitute(
            ProcessingJobId::generate(),
            ContentId::generate(),
            ProcessingJobType::Summary,
            $status,
            $progress,
            $createdAt,
            $updatedAt,
            $startedAt,
            $completedAt,
            $failedAt,
        );
    }
}

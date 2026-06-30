<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobProgress;
use App\Domain\Workspace\BatchJobStatus;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use PHPUnit\Framework\TestCase;

final class BatchJobTest extends TestCase
{
    public function testCreateBatchJob(): void
    {
        $job = $this->sampleJob();

        self::assertSame(BatchJobStatus::Pending, $job->status());
        self::assertSame(0, $job->progress()->percentage());
        self::assertSame(['fr', 'de'], $job->targetLanguages());
    }

    public function testStartBatchJob(): void
    {
        $job = $this->sampleJob()->start();

        self::assertSame(BatchJobStatus::Running, $job->status());
    }

    public function testResolveStatusThresholds(): void
    {
        self::assertSame(
            BatchJobStatus::Completed,
            BatchJob::resolveStatus(2, 0, 2),
        );
        self::assertSame(
            BatchJobStatus::PartialFailure,
            BatchJob::resolveStatus(1, 1, 2),
        );
        self::assertSame(
            BatchJobStatus::Failed,
            BatchJob::resolveStatus(0, 2, 2),
        );
        self::assertSame(
            BatchJobStatus::Running,
            BatchJob::resolveStatus(1, 0, 2),
        );
    }

    public function testProgressFromFinishedCount(): void
    {
        self::assertSame(50, BatchJobProgress::fromFinishedCount(1, 2)->percentage());
        self::assertSame(100, BatchJobProgress::fromFinishedCount(3, 3)->percentage());
    }

    public function testEmptyVideosThrows(): void
    {
        $this->expectException(InvalidProjectException::class);

        BatchJob::create(
            BatchJobId::generate(),
            ProjectId::generate(),
            [],
            ['fr'],
        );
    }

    private function sampleJob(): BatchJob
    {
        return BatchJob::create(
            BatchJobId::generate(),
            ProjectId::generate(),
            [
                new VideoId('550e8400-e29b-41d4-a716-446655440010'),
                new VideoId('550e8400-e29b-41d4-a716-446655440011'),
            ],
            ['fr', 'de'],
        );
    }
}

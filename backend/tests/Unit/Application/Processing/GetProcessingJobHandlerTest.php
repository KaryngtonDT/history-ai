<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Processing;

use App\Application\Processing\Handlers\GetProcessingJobHandler;
use App\Application\Processing\Queries\GetProcessingJobQuery;
use App\Domain\Content\ContentId;
use App\Domain\Processing\Exception\InvalidProcessingJobException;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobRepositoryInterface;
use App\Domain\Processing\ProcessingJobStatus;
use App\Domain\Processing\ProcessingJobType;
use PHPUnit\Framework\TestCase;

final class GetProcessingJobHandlerTest extends TestCase
{
    public function testReturnsProcessingJobResultWhenFound(): void
    {
        $jobId = ProcessingJobId::generate();
        $contentId = ContentId::generate();
        $job = ProcessingJob::create($jobId, $contentId, ProcessingJobType::Summary);

        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with(self::callback(static fn (ProcessingJobId $id): bool => $id->equals($jobId)))
            ->willReturn($job);

        $handler = new GetProcessingJobHandler($repository);

        $result = $handler(new GetProcessingJobQuery($jobId->value));

        self::assertNotNull($result);
        self::assertSame($jobId->value, $result->id);
        self::assertSame($contentId->value, $result->contentId);
        self::assertSame('summary', $result->type);
        self::assertSame(ProcessingJobStatus::Pending->value, $result->status);
        self::assertSame(0, $result->progress);
        self::assertNull($result->startedAt);
        self::assertNull($result->completedAt);
        self::assertNull($result->failedAt);
    }

    public function testReturnsNullWhenJobNotFound(): void
    {
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn(null);

        $handler = new GetProcessingJobHandler($repository);

        $result = $handler(new GetProcessingJobQuery(ProcessingJobId::generate()->value));

        self::assertNull($result);
    }

    public function testInvalidJobIdIsRejected(): void
    {
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository->expects(self::never())->method('findById');

        $handler = new GetProcessingJobHandler($repository);

        $this->expectException(InvalidProcessingJobException::class);

        $handler(new GetProcessingJobQuery('not-a-uuid'));
    }
}

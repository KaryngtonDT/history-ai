<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Processing;

use App\Application\Processing\Commands\CreateProcessingJobCommand;
use App\Application\Processing\Handlers\CreateProcessingJobHandler;
use App\Application\Processing\Ports\ProcessingOrchestratorInterface;
use App\Domain\Content\ContentId;
use App\Domain\Content\Exception\InvalidContentIdException;
use App\Domain\Processing\ProcessingJob;
use App\Domain\Processing\ProcessingJobRepositoryInterface;
use App\Domain\Processing\ProcessingJobStatus;
use App\Domain\Processing\ProcessingJobType;
use PHPUnit\Framework\TestCase;

final class CreateProcessingJobHandlerTest extends TestCase
{
    public function testCreatesProcessingJobAndReturnsResult(): void
    {
        $contentId = ContentId::generate();
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (ProcessingJob $job) use ($contentId): bool {
                return $contentId->equals($job->contentId())
                    && ProcessingJobType::Summary === $job->type()
                    && ProcessingJobStatus::Pending === $job->status()
                    && 0 === $job->progress()->percentage();
            }));

        $orchestrator = $this->createMock(ProcessingOrchestratorInterface::class);
        $orchestrator
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(ProcessingJob::class));

        $handler = new CreateProcessingJobHandler($repository, $orchestrator);

        $result = $handler(new CreateProcessingJobCommand(
            contentId: $contentId->value,
            type: ProcessingJobType::Summary,
        ));

        self::assertNotEmpty($result->processingJobId->value);
        self::assertSame(ProcessingJobStatus::Pending, $result->status);
        self::assertSame(0, $result->progress);
    }

    public function testSupportsDifferentProcessingJobTypes(): void
    {
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (ProcessingJob $job): bool {
                return ProcessingJobType::Quiz === $job->type();
            }));

        $orchestrator = $this->createMock(ProcessingOrchestratorInterface::class);
        $orchestrator->expects(self::once())->method('dispatch');

        $handler = new CreateProcessingJobHandler($repository, $orchestrator);

        $result = $handler(new CreateProcessingJobCommand(
            contentId: ContentId::generate()->value,
            type: ProcessingJobType::Quiz,
        ));

        self::assertSame(ProcessingJobStatus::Pending, $result->status);
    }

    public function testRepositorySaveIsCalledExactlyOnce(): void
    {
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository->expects(self::once())->method('save');

        $orchestrator = $this->createStub(ProcessingOrchestratorInterface::class);

        $handler = new CreateProcessingJobHandler($repository, $orchestrator);

        $handler(new CreateProcessingJobCommand(
            contentId: ContentId::generate()->value,
            type: ProcessingJobType::Podcast,
        ));
    }

    public function testInvalidContentIdIsRejected(): void
    {
        $repository = $this->createMock(ProcessingJobRepositoryInterface::class);
        $repository->expects(self::never())->method('save');

        $orchestrator = $this->createMock(ProcessingOrchestratorInterface::class);
        $orchestrator->expects(self::never())->method('dispatch');

        $handler = new CreateProcessingJobHandler($repository, $orchestrator);

        $this->expectException(InvalidContentIdException::class);

        $handler(new CreateProcessingJobCommand(
            contentId: 'not-a-valid-uuid',
            type: ProcessingJobType::Summary,
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Workspace;

use App\Application\Workspace\BatchJobProgressUpdater;
use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobRepositoryInterface;
use App\Domain\Workspace\ProjectId;
use PHPUnit\Framework\TestCase;

final class BatchJobProgressUpdaterTest extends TestCase
{
    public function testRecordsSuccessfulOutcome(): void
    {
        $repository = $this->createMock(BatchJobRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('recordVideoOutcome')
            ->with(
                self::callback(fn (BatchJobId $id): bool => '550e8400-e29b-41d4-a716-446655440050' === $id->value),
                new VideoId('550e8400-e29b-41d4-a716-446655440099'),
                true,
            )
            ->willReturn($this->sampleBatchJob());

        $updater = new BatchJobProgressUpdater($repository);
        $updater->recordOutcome(
            '550e8400-e29b-41d4-a716-446655440050',
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
            true,
        );
    }

    public function testIgnoresMissingBatchJobId(): void
    {
        $repository = $this->createMock(BatchJobRepositoryInterface::class);
        $repository->expects(self::never())->method('recordVideoOutcome');

        $updater = new BatchJobProgressUpdater($repository);
        $updater->recordOutcome(null, new VideoId('550e8400-e29b-41d4-a716-446655440099'), true);
    }

    private function sampleBatchJob(): BatchJob
    {
        return BatchJob::create(
            BatchJobId::generate(),
            ProjectId::generate(),
            [new VideoId('550e8400-e29b-41d4-a716-446655440099')],
            ['fr'],
        )->start();
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobProgress;
use App\Domain\Workspace\BatchJobRepositoryInterface;
use App\Domain\Workspace\BatchJobStatus;
use App\Domain\Workspace\ProjectId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineBatchJobRepository implements BatchJobRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(BatchJob $batchJob): void
    {
        $repository = $this->entityManager->getRepository(BatchJobRecord::class);
        $record = $repository->find($batchJob->id()->value);

        if (null === $record) {
            $this->entityManager->persist(BatchJobRecord::fromDomain($batchJob));
        } else {
            $record->applyDomain($batchJob);
        }

        $this->entityManager->flush();
    }

    public function findById(BatchJobId $id): ?BatchJob
    {
        $record = $this->entityManager->find(BatchJobRecord::class, $id->value);

        return $record?->toDomain();
    }

    public function findLatestByProjectId(ProjectId $projectId): ?BatchJob
    {
        $record = $this->entityManager->getRepository(BatchJobRecord::class)->findOneBy(
            ['projectId' => $projectId->value],
            ['createdAt' => 'DESC'],
        );

        return $record?->toDomain();
    }

    public function recordVideoOutcome(BatchJobId $batchJobId, VideoId $videoId, bool $success): ?BatchJob
    {
        $record = $this->entityManager->find(BatchJobRecord::class, $batchJobId->value);

        if (null === $record) {
            return null;
        }

        $outcomes = $record->outcomes();
        $outcomes[$videoId->value] = $success ? 'completed' : 'failed';
        $record->setOutcomes($outcomes);

        $succeeded = 0;
        $failed = 0;

        foreach ($record->videoIds() as $batchVideoId) {
            $outcome = $outcomes[$batchVideoId] ?? null;

            if ('completed' === $outcome) {
                ++$succeeded;
            }

            if ('failed' === $outcome) {
                ++$failed;
            }
        }

        $total = count($record->videoIds());
        $finished = $succeeded + $failed;
        $progress = BatchJobProgress::fromFinishedCount($finished, $total);
        $status = BatchJob::resolveStatus($succeeded, $failed, $total);
        $record->updateStatus($status, $progress);

        $this->entityManager->flush();

        return $record->toDomain();
    }
}

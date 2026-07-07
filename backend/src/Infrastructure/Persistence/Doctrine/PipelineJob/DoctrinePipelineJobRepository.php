<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobRepositoryInterface;
use App\Domain\PipelineJob\PipelineJobStatus;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePipelineJobRepository implements PipelineJobRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(PipelineJob $job): void
    {
        $repository = $this->entityManager->getRepository(PipelineJobRecord::class);
        $record = $repository->find($job->jobId()->value);

        if (null === $record) {
            $this->entityManager->persist(PipelineJobRecord::fromDomain($job));
        } else {
            $record->syncFromDomain($job);
        }

        $this->entityManager->flush();
    }

    public function findById(PipelineJobId $jobId): ?PipelineJob
    {
        $record = $this->entityManager->find(PipelineJobRecord::class, $jobId->value);

        return $record?->toDomain();
    }

    public function findActiveBySourceAndStage(string $sourceId, PipelineStageType $stage): ?PipelineJob
    {
        $qb = $this->entityManager->createQueryBuilder();
        $record = $qb
            ->select('j')
            ->from(PipelineJobRecord::class, 'j')
            ->where('j.sourceId = :sourceId')
            ->andWhere('j.stage = :stage')
            ->andWhere('j.status IN (:statuses)')
            ->setParameter('sourceId', $sourceId)
            ->setParameter('stage', $stage)
            ->setParameter('statuses', [
                PipelineJobStatus::Queued,
                PipelineJobStatus::Running,
                PipelineJobStatus::WaitingUserChoice,
                PipelineJobStatus::WaitingUserConfirmation,
            ])
            ->orderBy('j.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $record instanceof PipelineJobRecord ? $record->toDomain() : null;
    }

    public function findBySourceId(string $sourceId): array
    {
        $records = $this->entityManager->getRepository(PipelineJobRecord::class)->findBy(
            ['sourceId' => $sourceId],
            ['updatedAt' => 'DESC'],
        );

        return array_map(static fn (PipelineJobRecord $r): PipelineJob => $r->toDomain(), $records);
    }

    public function findActiveBySourceId(string $sourceId): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        /** @var list<PipelineJobRecord> $records */
        $records = $qb
            ->select('j')
            ->from(PipelineJobRecord::class, 'j')
            ->where('j.sourceId = :sourceId')
            ->andWhere('j.status IN (:statuses)')
            ->setParameter('sourceId', $sourceId)
            ->setParameter('statuses', [
                PipelineJobStatus::Queued,
                PipelineJobStatus::Running,
                PipelineJobStatus::WaitingUserChoice,
                PipelineJobStatus::WaitingUserConfirmation,
            ])
            ->orderBy('j.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(static fn (PipelineJobRecord $r): PipelineJob => $r->toDomain(), $records);
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\EngineAnalytics;

use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionHistoryId;
use App\Domain\EngineAnalytics\EngineExecutionHistoryRepositoryInterface;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineEngineExecutionHistoryRepository implements EngineExecutionHistoryRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function record(EngineExecutionHistory $execution): void
    {
        $this->entityManager->persist(EngineExecutionHistoryRecord::fromDomain($execution));
        $this->entityManager->flush();
    }

    public function findById(EngineExecutionHistoryId $executionId): ?EngineExecutionHistory
    {
        $record = $this->entityManager->find(EngineExecutionHistoryRecord::class, $executionId->value);

        return $record?->toDomain();
    }

    public function findLatestByPipelineJobId(PipelineJobId $pipelineJobId): ?EngineExecutionHistory
    {
        $record = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(EngineExecutionHistoryRecord::class, 'e')
            ->where('e.pipelineJobId = :jobId')
            ->setParameter('jobId', $pipelineJobId->value)
            ->orderBy('e.completedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $record?->toDomain();
    }

    public function findRecent(
        ?PipelineStageType $stage = null,
        ?string $engineId = null,
        ?string $hardwareProfile = null,
        int $limit = 20,
    ): array {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(EngineExecutionHistoryRecord::class, 'e')
            ->orderBy('e.completedAt', 'DESC')
            ->setMaxResults($limit);

        if (null !== $stage) {
            $qb->andWhere('e.stage = :stage')->setParameter('stage', $stage);
        }

        if (null !== $engineId) {
            $qb->andWhere('e.engineId = :engineId')->setParameter('engineId', $engineId);
        }

        if (null !== $hardwareProfile) {
            $qb->andWhere('e.hardwareProfile = :hardwareProfile')->setParameter('hardwareProfile', $hardwareProfile);
        }

        /** @var list<EngineExecutionHistoryRecord> $records */
        $records = $qb->getQuery()->getResult();

        return array_map(static fn (EngineExecutionHistoryRecord $record): EngineExecutionHistory => $record->toDomain(), $records);
    }

    public function findByEngineId(string $engineId, int $limit = 50): array
    {
        return $this->findRecent(engineId: $engineId, limit: $limit);
    }
}

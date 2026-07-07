<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\PipelineJob;

use App\Domain\PipelineJob\PipelineNotification;
use App\Domain\PipelineJob\PipelineNotificationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePipelineNotificationRepository implements PipelineNotificationRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(PipelineNotification $notification): void
    {
        $repository = $this->entityManager->getRepository(PipelineNotificationRecord::class);
        $record = $repository->find($notification->notificationId()->value);

        if (null === $record) {
            $this->entityManager->persist(PipelineNotificationRecord::fromDomain($notification));
        } else {
            $record->syncFromDomain($notification);
        }

        $this->entityManager->flush();
    }

    public function findBySourceId(string $sourceId, int $limit = 50): array
    {
        $records = $this->entityManager->getRepository(PipelineNotificationRecord::class)->findBy(
            ['sourceId' => $sourceId],
            ['createdAt' => 'DESC'],
            $limit,
        );

        return array_map(static fn (PipelineNotificationRecord $r): PipelineNotification => $r->toDomain(), $records);
    }

    public function findUnreadBySourceId(string $sourceId): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        /** @var list<PipelineNotificationRecord> $records */
        $records = $qb
            ->select('n')
            ->from(PipelineNotificationRecord::class, 'n')
            ->where('n.sourceId = :sourceId')
            ->andWhere('n.readFlag = false')
            ->setParameter('sourceId', $sourceId)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(static fn (PipelineNotificationRecord $r): PipelineNotification => $r->toDomain(), $records);
    }
}

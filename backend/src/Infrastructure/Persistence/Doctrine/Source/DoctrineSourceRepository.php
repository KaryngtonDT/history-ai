<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Source;

use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSourceRepository implements SourceRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Source $source): void
    {
        $repository = $this->entityManager->getRepository(SourceRecord::class);
        $record = $repository->find($source->id()->value);

        if (null === $record) {
            $this->entityManager->persist(SourceRecord::fromDomain($source));
        } else {
            $record->syncFromDomain($source);
        }

        $this->entityManager->flush();
    }

    public function findById(SourceId $id): ?Source
    {
        $record = $this->entityManager->find(SourceRecord::class, $id->value);

        return $record?->toDomain();
    }

    public function delete(SourceId $id): void
    {
        $record = $this->entityManager->find(SourceRecord::class, $id->value);

        if (null === $record) {
            return;
        }

        $this->entityManager->remove($record);
        $this->entityManager->flush();
    }

    public function findByType(SourceType $type, int $limit = 20): array
    {
        /** @var list<SourceRecord> $records */
        $records = $this->entityManager->createQueryBuilder()
            ->select('source')
            ->from(SourceRecord::class, 'source')
            ->where('source.type = :type')
            ->setParameter('type', $type)
            ->orderBy('source.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(static fn (SourceRecord $record): Source => $record->toDomain(), $records);
    }
}

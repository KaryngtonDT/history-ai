<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Collection;

use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCollectionRepository implements CollectionRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Collection $collection): void
    {
        $repository = $this->entityManager->getRepository(CollectionRecord::class);
        $record = $repository->find($collection->id()->value);

        if (null === $record) {
            $this->entityManager->persist(CollectionRecord::fromDomain($collection));
        }

        $this->entityManager->flush();
    }

    public function findById(CollectionId $id): ?Collection
    {
        $record = $this->entityManager->find(CollectionRecord::class, $id->value);

        return $record?->toDomain();
    }

    public function findAll(): array
    {
        /** @var list<CollectionRecord> $records */
        $records = $this->entityManager->getRepository(CollectionRecord::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
        );

        return array_map(
            static fn (CollectionRecord $record): Collection => $record->toDomain(),
            $records,
        );
    }
}

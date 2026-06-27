<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\CollectionItem;

use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItem;
use App\Domain\CollectionItem\CollectionItemRepositoryInterface;
use App\Domain\Library\LibraryItemId;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCollectionItemRepository implements CollectionItemRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(CollectionItem $item): void
    {
        $repository = $this->entityManager->getRepository(CollectionItemRecord::class);
        $record = $repository->find($item->id()->value);

        if (null === $record) {
            $this->entityManager->persist(CollectionItemRecord::fromDomain($item));
        }

        $this->entityManager->flush();
    }

    public function exists(CollectionId $collectionId, LibraryItemId $libraryItemId): bool
    {
        $record = $this->entityManager->getRepository(CollectionItemRecord::class)->findOneBy([
            'collectionId' => $collectionId->value,
            'libraryItemId' => $libraryItemId->value,
        ]);

        return null !== $record;
    }

    public function findByCollectionId(CollectionId $collectionId): array
    {
        /** @var list<CollectionItemRecord> $records */
        $records = $this->entityManager->getRepository(CollectionItemRecord::class)->findBy(
            ['collectionId' => $collectionId->value],
            ['createdAt' => 'DESC'],
        );

        return array_map(
            static fn (CollectionItemRecord $record): CollectionItem => $record->toDomain(),
            $records,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Library;

use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineLibraryRepository implements LibraryItemRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function save(LibraryItem $item): void
    {
        $repository = $this->entityManager->getRepository(LibraryItemRecord::class);
        $record = $repository->find($item->id()->value);

        if (null === $record) {
            $this->entityManager->persist(LibraryItemRecord::fromDomain($item));
        }

        $this->entityManager->flush();
    }

    public function findById(LibraryItemId $id): ?LibraryItem
    {
        $record = $this->entityManager->find(LibraryItemRecord::class, $id->value);

        return $record?->toDomain();
    }

    public function findAll(): array
    {
        /** @var list<LibraryItemRecord> $records */
        $records = $this->entityManager->getRepository(LibraryItemRecord::class)->findBy(
            [],
            ['createdAt' => 'DESC'],
        );

        return array_map(
            static fn (LibraryItemRecord $record): LibraryItem => $record->toDomain(),
            $records,
        );
    }
}

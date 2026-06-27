<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\CollectionItem;

use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItem;
use App\Domain\CollectionItem\CollectionItemId;
use App\Domain\CollectionItem\CollectionItemRepositoryInterface;
use App\Domain\Library\LibraryItemId;
use App\Infrastructure\Persistence\Doctrine\CollectionItem\CollectionItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineCollectionItemRepositoryTest extends KernelTestCase
{
    private CollectionItemRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(CollectionItemRepositoryInterface::class);
    }

    public function testSaveAndExists(): void
    {
        $collectionId = CollectionId::generate();
        $libraryItemId = LibraryItemId::generate();
        $item = CollectionItem::create(
            CollectionItemId::generate(),
            $collectionId,
            $libraryItemId,
        );

        self::assertFalse($this->repository->exists($collectionId, $libraryItemId));

        $this->repository->save($item);

        self::assertTrue($this->repository->exists($collectionId, $libraryItemId));
    }

    public function testFindByCollectionIdReturnsAssignedItems(): void
    {
        $collectionId = CollectionId::generate();
        $otherCollectionId = CollectionId::generate();
        $firstLibraryItemId = LibraryItemId::generate();
        $secondLibraryItemId = LibraryItemId::generate();

        $older = CollectionItem::reconstitute(
            CollectionItemId::generate(),
            $collectionId,
            $firstLibraryItemId,
            new \DateTimeImmutable('2026-06-27 10:00:00'),
        );
        $newer = CollectionItem::reconstitute(
            CollectionItemId::generate(),
            $collectionId,
            $secondLibraryItemId,
            new \DateTimeImmutable('2026-06-27 12:00:00'),
        );
        $otherCollectionItem = CollectionItem::create(
            CollectionItemId::generate(),
            $otherCollectionId,
            LibraryItemId::generate(),
        );

        $this->repository->save($older);
        $this->repository->save($newer);
        $this->repository->save($otherCollectionItem);

        $found = $this->repository->findByCollectionId($collectionId);

        self::assertCount(2, $found);
        self::assertTrue($found[0]->libraryItemId()->equals($secondLibraryItemId));
        self::assertTrue($found[1]->libraryItemId()->equals($firstLibraryItemId));
    }

    public function testSaveIsIdempotentForExistingCollectionItem(): void
    {
        $item = CollectionItem::create(
            CollectionItemId::generate(),
            CollectionId::generate(),
            LibraryItemId::generate(),
        );

        $this->repository->save($item);
        $this->repository->save($item);

        $found = $this->repository->findByCollectionId($item->collectionId());

        self::assertCount(1, $found);
        self::assertTrue($found[0]->id()->equals($item->id()));
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(CollectionItemRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}

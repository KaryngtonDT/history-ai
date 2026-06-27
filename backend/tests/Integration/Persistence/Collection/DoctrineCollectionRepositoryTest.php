<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Collection;

use App\Domain\Collection\Collection;
use App\Domain\Collection\CollectionDescription;
use App\Domain\Collection\CollectionId;
use App\Domain\Collection\CollectionName;
use App\Domain\Collection\CollectionRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Collection\CollectionRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineCollectionRepositoryTest extends KernelTestCase
{
    private CollectionRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(CollectionRepositoryInterface::class);
    }

    public function testSaveAndFindById(): void
    {
        $collectionId = CollectionId::generate();
        $collection = Collection::create(
            $collectionId,
            new CollectionName('Ancient Rome'),
            new CollectionDescription('Roman history and culture.'),
        );

        $this->repository->save($collection);

        $found = $this->repository->findById($collectionId);

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($collectionId));
        self::assertSame('Ancient Rome', $found->name()->value);
        self::assertSame('Roman history and culture.', $found->description()->value);
        self::assertSame(
            $collection->createdAt()->format('Y-m-d H:i:s'),
            $found->createdAt()->format('Y-m-d H:i:s'),
        );
    }

    public function testFindByIdReturnsNullWhenMissing(): void
    {
        self::assertNull($this->repository->findById(CollectionId::generate()));
    }

    public function testFindAllOrdersByCreatedAtDescending(): void
    {
        $older = Collection::reconstitute(
            CollectionId::generate(),
            new CollectionName('Philosophy'),
            new CollectionDescription('Philosophy resources.'),
            new \DateTimeImmutable('2026-06-27 10:00:00'),
        );
        $newer = Collection::reconstitute(
            CollectionId::generate(),
            new CollectionName('Languages'),
            new CollectionDescription('Language learning materials.'),
            new \DateTimeImmutable('2026-06-27 12:00:00'),
        );

        $this->repository->save($older);
        $this->repository->save($newer);

        $found = $this->repository->findAll();

        self::assertCount(2, $found);
        self::assertTrue($found[0]->id()->equals($newer->id()));
        self::assertTrue($found[1]->id()->equals($older->id()));
    }

    public function testSaveIsIdempotentForExistingCollection(): void
    {
        $collection = Collection::create(
            CollectionId::generate(),
            new CollectionName('Ancient Greece'),
            new CollectionDescription('Greek history materials.'),
        );

        $this->repository->save($collection);
        $this->repository->save($collection);

        $found = $this->repository->findById($collection->id());

        self::assertNotNull($found);
        self::assertSame('Ancient Greece', $found->name()->value);
        self::assertSame('Greek history materials.', $found->description()->value);
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(CollectionRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Library;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use App\Infrastructure\Persistence\Doctrine\Library\LibraryItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineLibraryRepositoryTest extends KernelTestCase
{
    private LibraryItemRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->repository = static::getContainer()->get(LibraryItemRepositoryInterface::class);
    }

    public function testSaveAndFindById(): void
    {
        $itemId = LibraryItemId::generate();
        $contentId = ContentId::generate();
        $artifactId = ArtifactId::generate();
        $item = LibraryItem::create(
            $itemId,
            $contentId,
            $artifactId,
            LibraryItemType::Summary,
            new LibraryItemTitle('Roman Empire Summary'),
        );

        $this->repository->save($item);

        $found = $this->repository->findById($itemId);

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($itemId));
        self::assertTrue($found->contentId()->equals($contentId));
        self::assertTrue($found->artifactId()->equals($artifactId));
        self::assertSame(LibraryItemType::Summary, $found->type());
        self::assertSame('Roman Empire Summary', $found->title()->value);
        self::assertSame(
            $item->createdAt()->format('Y-m-d H:i:s'),
            $found->createdAt()->format('Y-m-d H:i:s'),
        );
    }

    public function testFindByIdReturnsNullWhenMissing(): void
    {
        self::assertNull($this->repository->findById(LibraryItemId::generate()));
    }

    public function testFindAllOrdersByCreatedAtDescending(): void
    {
        $older = LibraryItem::reconstitute(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Summary,
            new LibraryItemTitle('Older summary'),
            new \DateTimeImmutable('2026-06-26 10:00:00'),
        );
        $newer = LibraryItem::reconstitute(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Quiz,
            new LibraryItemTitle('Newer quiz'),
            new \DateTimeImmutable('2026-06-26 12:00:00'),
        );

        $this->repository->save($older);
        $this->repository->save($newer);

        $found = $this->repository->findAll();

        self::assertCount(2, $found);
        self::assertTrue($found[0]->id()->equals($newer->id()));
        self::assertTrue($found[1]->id()->equals($older->id()));
    }

    public function testSaveIsIdempotentForExistingLibraryItem(): void
    {
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Flashcards,
            new LibraryItemTitle('Flashcards: Roman Empire'),
        );

        $this->repository->save($item);
        $this->repository->save($item);

        $found = $this->repository->findById($item->id());

        self::assertNotNull($found);
        self::assertSame('Flashcards: Roman Empire', $found->title()->value);
        self::assertSame(LibraryItemType::Flashcards, $found->type());
    }

    private function resetDatabaseSchema(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getMetadataFor(LibraryItemRecord::class);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema([$metadata]);
        $schemaTool->createSchema([$metadata]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence\Search;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use App\Domain\Search\LibrarySearchRepositoryInterface;
use App\Domain\Search\SearchQuery;
use App\Infrastructure\Persistence\Doctrine\Library\LibraryItemRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DoctrineLibrarySearchRepositoryTest extends KernelTestCase
{
    private LibraryItemRepositoryInterface $libraryRepository;

    private LibrarySearchRepositoryInterface $searchRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resetDatabaseSchema();
        $this->libraryRepository = static::getContainer()->get(LibraryItemRepositoryInterface::class);
        $this->searchRepository = static::getContainer()->get(LibrarySearchRepositoryInterface::class);
    }

    public function testSearchFindsExactTitleMatch(): void
    {
        $item = $this->createLibraryItem('Roman Empire Summary');

        $this->libraryRepository->save($item);

        $results = $this->searchRepository->search(new SearchQuery('Roman Empire Summary'));

        self::assertCount(1, $results);
        self::assertTrue($results[0]->id()->equals($item->id()));
    }

    public function testSearchFindsPartialTitleMatch(): void
    {
        $item = $this->createLibraryItem('Roman Empire Summary');

        $this->libraryRepository->save($item);

        $results = $this->searchRepository->search(new SearchQuery('Empire'));

        self::assertCount(1, $results);
        self::assertTrue($results[0]->id()->equals($item->id()));
    }

    public function testSearchIsCaseInsensitive(): void
    {
        $item = $this->createLibraryItem('Roman Empire Summary');

        $this->libraryRepository->save($item);

        $results = $this->searchRepository->search(new SearchQuery('roman empire'));

        self::assertCount(1, $results);
        self::assertTrue($results[0]->id()->equals($item->id()));
    }

    public function testSearchReturnsEmptyArrayWhenNoMatch(): void
    {
        $this->libraryRepository->save($this->createLibraryItem('Roman Empire Summary'));

        $results = $this->searchRepository->search(new SearchQuery('Byzantine'));

        self::assertSame([], $results);
    }

    public function testSearchOrdersResultsByNewestFirst(): void
    {
        $older = LibraryItem::reconstitute(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Summary,
            new LibraryItemTitle('Roman Empire Summary'),
            new \DateTimeImmutable('2026-06-26 10:00:00'),
        );
        $newer = LibraryItem::reconstitute(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Quiz,
            new LibraryItemTitle('Roman Empire Quiz'),
            new \DateTimeImmutable('2026-06-26 12:00:00'),
        );

        $this->libraryRepository->save($older);
        $this->libraryRepository->save($newer);

        $results = $this->searchRepository->search(new SearchQuery('Roman Empire'));

        self::assertCount(2, $results);
        self::assertTrue($results[0]->id()->equals($newer->id()));
        self::assertTrue($results[1]->id()->equals($older->id()));
    }

    private function createLibraryItem(string $title): LibraryItem
    {
        return LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Summary,
            new LibraryItemTitle($title),
        );
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

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Search;

use App\Application\Search\DTO\SearchLibraryItem;
use App\Application\Search\Handlers\SearchLibraryHandler;
use App\Application\Search\Queries\SearchLibraryQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use App\Domain\Search\LibrarySearchRepositoryInterface;
use App\Domain\Search\SearchQuery;
use PHPUnit\Framework\TestCase;

final class SearchLibraryHandlerTest extends TestCase
{
    public function testReturnsSearchResults(): void
    {
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Summary,
            new LibraryItemTitle('Roman Empire Summary'),
        );
        $searchQuery = new SearchQuery('Roman');

        $repository = $this->createStub(LibrarySearchRepositoryInterface::class);
        $repository
            ->method('search')
            ->willReturn([$item]);

        $handler = new SearchLibraryHandler($repository);
        $result = $handler(new SearchLibraryQuery($searchQuery));

        self::assertCount(1, $result->items);
        self::assertInstanceOf(SearchLibraryItem::class, $result->items[0]);
        self::assertSame($item->id()->value, $result->items[0]->id);
        self::assertSame($item->contentId()->value, $result->items[0]->contentId);
        self::assertSame($item->artifactId()->value, $result->items[0]->artifactId);
        self::assertSame('summary', $result->items[0]->type);
        self::assertSame('Roman Empire Summary', $result->items[0]->title);
        self::assertNotSame('', $result->items[0]->createdAt);
    }

    public function testReturnsEmptyResultWhenNoMatches(): void
    {
        $searchQuery = new SearchQuery('Byzantine');

        $repository = $this->createStub(LibrarySearchRepositoryInterface::class);
        $repository
            ->method('search')
            ->willReturn([]);

        $handler = new SearchLibraryHandler($repository);
        $result = $handler(new SearchLibraryQuery($searchQuery));

        self::assertSame([], $result->items);
    }
}

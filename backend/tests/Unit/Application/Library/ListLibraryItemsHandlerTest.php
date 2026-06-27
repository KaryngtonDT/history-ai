<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Library;

use App\Application\Library\DTO\LibraryItemListItem;
use App\Application\Library\Handlers\ListLibraryItemsHandler;
use App\Application\Library\Queries\ListLibraryItemsQuery;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Content\ContentId;
use App\Domain\Library\LibraryItem;
use App\Domain\Library\LibraryItemId;
use App\Domain\Library\LibraryItemRepositoryInterface;
use App\Domain\Library\LibraryItemTitle;
use App\Domain\Library\LibraryItemType;
use PHPUnit\Framework\TestCase;

final class ListLibraryItemsHandlerTest extends TestCase
{
    public function testReturnsEmptyListWhenNoLibraryItemsExist(): void
    {
        $repository = $this->createStub(LibraryItemRepositoryInterface::class);
        $repository->method('findAll')->willReturn([]);

        $handler = new ListLibraryItemsHandler($repository);
        $result = $handler(new ListLibraryItemsQuery());

        self::assertSame([], $result->items);
    }

    public function testReturnsItemsFromRepository(): void
    {
        $item = LibraryItem::create(
            LibraryItemId::generate(),
            ContentId::generate(),
            ArtifactId::generate(),
            LibraryItemType::Summary,
            new LibraryItemTitle('Roman Empire Summary'),
        );

        $repository = $this->createStub(LibraryItemRepositoryInterface::class);
        $repository->method('findAll')->willReturn([$item]);

        $handler = new ListLibraryItemsHandler($repository);
        $result = $handler(new ListLibraryItemsQuery());

        self::assertCount(1, $result->items);
        self::assertInstanceOf(LibraryItemListItem::class, $result->items[0]);
        self::assertSame($item->id()->value, $result->items[0]->id);
        self::assertSame($item->contentId()->value, $result->items[0]->contentId);
        self::assertSame($item->artifactId()->value, $result->items[0]->artifactId);
        self::assertSame('summary', $result->items[0]->type);
        self::assertSame('Roman Empire Summary', $result->items[0]->title);
    }
}

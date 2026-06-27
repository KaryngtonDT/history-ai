<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Collection;

use App\Application\Collection\Commands\AssignLibraryItemToCollectionCommand;
use App\Application\Collection\Handlers\AssignLibraryItemToCollectionHandler;
use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItem;
use App\Domain\CollectionItem\CollectionItemRepositoryInterface;
use App\Domain\CollectionItem\Exception\CollectionItemAlreadyExistsException;
use App\Domain\Library\LibraryItemId;
use PHPUnit\Framework\TestCase;

final class AssignLibraryItemToCollectionHandlerTest extends TestCase
{
    public function testAssignSucceeds(): void
    {
        $collectionId = CollectionId::generate();
        $libraryItemId = LibraryItemId::generate();
        $repository = $this->createMock(CollectionItemRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('exists')
            ->with($collectionId, $libraryItemId)
            ->willReturn(false);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (CollectionItem $item) use ($collectionId, $libraryItemId): bool {
                return $collectionId->equals($item->collectionId())
                    && $libraryItemId->equals($item->libraryItemId());
            }));

        $handler = new AssignLibraryItemToCollectionHandler($repository);

        $result = $handler(new AssignLibraryItemToCollectionCommand(
            collectionId: $collectionId->value,
            libraryItemId: $libraryItemId->value,
        ));

        self::assertNotEmpty($result->collectionItemId->value);
        self::assertTrue($result->collectionId->equals($collectionId));
        self::assertTrue($result->libraryItemId->equals($libraryItemId));
        self::assertInstanceOf(\DateTimeImmutable::class, $result->createdAt);
    }

    public function testDuplicateAssignmentIsRejected(): void
    {
        $collectionId = CollectionId::generate();
        $libraryItemId = LibraryItemId::generate();
        $repository = $this->createMock(CollectionItemRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('exists')
            ->with($collectionId, $libraryItemId)
            ->willReturn(true);
        $repository->expects(self::never())->method('save');

        $handler = new AssignLibraryItemToCollectionHandler($repository);

        $this->expectException(CollectionItemAlreadyExistsException::class);
        $this->expectExceptionMessage('Library item is already assigned to this collection.');

        $handler(new AssignLibraryItemToCollectionCommand(
            collectionId: $collectionId->value,
            libraryItemId: $libraryItemId->value,
        ));
    }

    public function testRepositorySaveIsCalledExactlyOnce(): void
    {
        $repository = $this->createMock(CollectionItemRepositoryInterface::class);
        $repository->method('exists')->willReturn(false);
        $repository->expects(self::once())->method('save');

        $handler = new AssignLibraryItemToCollectionHandler($repository);

        $handler(new AssignLibraryItemToCollectionCommand(
            collectionId: CollectionId::generate()->value,
            libraryItemId: LibraryItemId::generate()->value,
        ));
    }
}

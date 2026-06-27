<?php

declare(strict_types=1);

namespace App\Application\Collection\Handlers;

use App\Application\Collection\Commands\AssignLibraryItemToCollectionCommand;
use App\Application\Collection\DTO\AssignLibraryItemToCollectionResult;
use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItem;
use App\Domain\CollectionItem\CollectionItemId;
use App\Domain\CollectionItem\CollectionItemRepositoryInterface;
use App\Domain\CollectionItem\Exception\CollectionItemAlreadyExistsException;
use App\Domain\Library\LibraryItemId;

final class AssignLibraryItemToCollectionHandler
{
    public function __construct(
        private readonly CollectionItemRepositoryInterface $collectionItemRepository,
    ) {
    }

    public function __invoke(AssignLibraryItemToCollectionCommand $command): AssignLibraryItemToCollectionResult
    {
        $collectionId = new CollectionId($command->collectionId);
        $libraryItemId = new LibraryItemId($command->libraryItemId);

        if ($this->collectionItemRepository->exists($collectionId, $libraryItemId)) {
            throw new CollectionItemAlreadyExistsException(
                'Library item is already assigned to this collection.',
            );
        }

        $collectionItem = CollectionItem::create(
            CollectionItemId::generate(),
            $collectionId,
            $libraryItemId,
        );

        $this->collectionItemRepository->save($collectionItem);

        return new AssignLibraryItemToCollectionResult(
            $collectionItem->id(),
            $collectionItem->collectionId(),
            $collectionItem->libraryItemId(),
            $collectionItem->createdAt(),
        );
    }
}

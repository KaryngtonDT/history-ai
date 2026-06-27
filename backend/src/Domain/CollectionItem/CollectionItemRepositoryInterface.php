<?php

declare(strict_types=1);

namespace App\Domain\CollectionItem;

use App\Domain\Collection\CollectionId;
use App\Domain\Library\LibraryItemId;

interface CollectionItemRepositoryInterface
{
    public function save(CollectionItem $item): void;

    public function exists(CollectionId $collectionId, LibraryItemId $libraryItemId): bool;

    /**
     * @return list<CollectionItem>
     */
    public function findByCollectionId(CollectionId $collectionId): array;
}

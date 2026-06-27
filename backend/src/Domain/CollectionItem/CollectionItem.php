<?php

declare(strict_types=1);

namespace App\Domain\CollectionItem;

use App\Domain\Collection\CollectionId;
use App\Domain\Library\LibraryItemId;
use DateTimeImmutable;

final class CollectionItem
{
    private function __construct(
        private readonly CollectionItemId $id,
        private readonly CollectionId $collectionId,
        private readonly LibraryItemId $libraryItemId,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        CollectionItemId $id,
        CollectionId $collectionId,
        LibraryItemId $libraryItemId,
    ): self {
        return new self(
            $id,
            $collectionId,
            $libraryItemId,
            new DateTimeImmutable(),
        );
    }

    /**
     * Rebuilds a CollectionItem aggregate from persistence. Used by infrastructure only.
     */
    public static function reconstitute(
        CollectionItemId $id,
        CollectionId $collectionId,
        LibraryItemId $libraryItemId,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $collectionId,
            $libraryItemId,
            $createdAt,
        );
    }

    public function id(): CollectionItemId
    {
        return $this->id;
    }

    public function collectionId(): CollectionId
    {
        return $this->collectionId;
    }

    public function libraryItemId(): LibraryItemId
    {
        return $this->libraryItemId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

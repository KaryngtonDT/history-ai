<?php

declare(strict_types=1);

namespace App\Application\Collection\DTO;

use App\Domain\Collection\CollectionId;
use App\Domain\CollectionItem\CollectionItemId;
use App\Domain\Library\LibraryItemId;
use DateTimeImmutable;

final readonly class AssignLibraryItemToCollectionResult
{
    public function __construct(
        public CollectionItemId $collectionItemId,
        public CollectionId $collectionId,
        public LibraryItemId $libraryItemId,
        public DateTimeImmutable $createdAt,
    ) {
    }
}

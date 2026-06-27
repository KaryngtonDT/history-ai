<?php

declare(strict_types=1);

namespace App\Application\Collection\Commands;

final readonly class AssignLibraryItemToCollectionCommand
{
    public function __construct(
        public string $collectionId,
        public string $libraryItemId,
    ) {
    }
}

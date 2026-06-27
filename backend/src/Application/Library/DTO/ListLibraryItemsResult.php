<?php

declare(strict_types=1);

namespace App\Application\Library\DTO;

final readonly class ListLibraryItemsResult
{
    /**
     * @param list<LibraryItemListItem> $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}

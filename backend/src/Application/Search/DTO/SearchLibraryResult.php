<?php

declare(strict_types=1);

namespace App\Application\Search\DTO;

final readonly class SearchLibraryResult
{
    /**
     * @param list<SearchLibraryItem> $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}

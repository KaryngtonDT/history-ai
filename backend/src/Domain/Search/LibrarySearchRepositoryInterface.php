<?php

declare(strict_types=1);

namespace App\Domain\Search;

use App\Domain\Library\LibraryItem;

interface LibrarySearchRepositoryInterface
{
    /**
     * @return list<LibraryItem>
     */
    public function search(SearchQuery $query): array;
}

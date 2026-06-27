<?php

declare(strict_types=1);

namespace App\Application\Search\Queries;

use App\Domain\Search\SearchQuery;

final readonly class SearchLibraryQuery
{
    public function __construct(
        public SearchQuery $searchQuery,
    ) {
    }
}

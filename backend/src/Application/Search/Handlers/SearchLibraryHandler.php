<?php

declare(strict_types=1);

namespace App\Application\Search\Handlers;

use App\Application\Search\DTO\SearchLibraryItem;
use App\Application\Search\DTO\SearchLibraryResult;
use App\Application\Search\Queries\SearchLibraryQuery;
use App\Domain\Search\LibrarySearchRepositoryInterface;

final class SearchLibraryHandler
{
    public function __construct(
        private readonly LibrarySearchRepositoryInterface $searchRepository,
    ) {
    }

    public function __invoke(SearchLibraryQuery $query): SearchLibraryResult
    {
        $items = array_map(
            static fn ($item): SearchLibraryItem => SearchLibraryItem::fromDomain($item),
            $this->searchRepository->search($query->searchQuery),
        );

        return new SearchLibraryResult($items);
    }
}

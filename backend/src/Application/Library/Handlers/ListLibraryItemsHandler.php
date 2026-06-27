<?php

declare(strict_types=1);

namespace App\Application\Library\Handlers;

use App\Application\Library\DTO\LibraryItemListItem;
use App\Application\Library\DTO\ListLibraryItemsResult;
use App\Application\Library\Queries\ListLibraryItemsQuery;
use App\Domain\Library\LibraryItemRepositoryInterface;

final class ListLibraryItemsHandler
{
    public function __construct(
        private readonly LibraryItemRepositoryInterface $libraryItemRepository,
    ) {
    }

    public function __invoke(ListLibraryItemsQuery $query): ListLibraryItemsResult
    {
        $items = array_map(
            static fn ($item): LibraryItemListItem => LibraryItemListItem::fromDomain($item),
            $this->libraryItemRepository->findAll(),
        );

        return new ListLibraryItemsResult($items);
    }
}

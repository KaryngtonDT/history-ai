<?php

declare(strict_types=1);

namespace App\Application\Collection\Handlers;

use App\Application\Collection\DTO\CollectionListItem;
use App\Application\Collection\DTO\ListCollectionsResult;
use App\Application\Collection\Queries\ListCollectionsQuery;
use App\Domain\Collection\CollectionRepositoryInterface;

final class ListCollectionsHandler
{
    public function __construct(
        private readonly CollectionRepositoryInterface $collectionRepository,
    ) {
    }

    public function __invoke(ListCollectionsQuery $query): ListCollectionsResult
    {
        $collections = array_map(
            static fn ($collection): CollectionListItem => CollectionListItem::fromDomain($collection),
            $this->collectionRepository->findAll(),
        );

        return new ListCollectionsResult($collections);
    }
}

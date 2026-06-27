<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Collection;

use App\Application\Collection\DTO\CollectionListItem;
use App\Application\Collection\DTO\ListCollectionsResult;

final class ListCollectionsResponse
{
    /**
     * @return list<array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     createdAt: string
     * }>
     */
    public static function fromResult(ListCollectionsResult $result): array
    {
        return array_map(
            static fn (CollectionListItem $collection): array => $collection->toArray(),
            $result->collections,
        );
    }
}

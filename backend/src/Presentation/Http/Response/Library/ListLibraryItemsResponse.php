<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Library;

use App\Application\Library\DTO\LibraryItemListItem;
use App\Application\Library\DTO\ListLibraryItemsResult;

final class ListLibraryItemsResponse
{
    /**
     * @return list<array{
     *     id: string,
     *     contentId: string,
     *     artifactId: string,
     *     type: string,
     *     title: string,
     *     createdAt: string
     * }>
     */
    public static function fromResult(ListLibraryItemsResult $result): array
    {
        return array_map(
            static fn (LibraryItemListItem $item): array => $item->toArray(),
            $result->items,
        );
    }
}

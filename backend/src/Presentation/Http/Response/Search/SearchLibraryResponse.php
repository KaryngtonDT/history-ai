<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Search;

use App\Application\Search\DTO\SearchLibraryItem;
use App\Application\Search\DTO\SearchLibraryResult;

final class SearchLibraryResponse
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
    public static function fromResult(SearchLibraryResult $result): array
    {
        return array_map(
            static fn (SearchLibraryItem $item): array => $item->toArray(),
            $result->items,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Content;

use App\Application\Content\DTO\ContentListItem;
use App\Application\Content\DTO\ListContentsResult;

final class ListContentsResponse
{
    /**
     * @return list<array{
     *     id: string,
     *     title: string,
     *     sourceType: string,
     *     status: string,
     *     createdAt: string,
     *     updatedAt: string
     * }>
     */
    public static function fromResult(ListContentsResult $result): array
    {
        return array_map(
            static fn (ContentListItem $item): array => $item->toArray(),
            $result->items,
        );
    }
}

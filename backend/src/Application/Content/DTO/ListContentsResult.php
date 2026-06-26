<?php

declare(strict_types=1);

namespace App\Application\Content\DTO;

final readonly class ListContentsResult
{
    /**
     * @param list<ContentListItem> $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}

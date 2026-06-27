<?php

declare(strict_types=1);

namespace App\Application\Collection\DTO;

final readonly class ListCollectionsResult
{
    /**
     * @param list<CollectionListItem> $collections
     */
    public function __construct(
        public array $collections,
    ) {
    }
}

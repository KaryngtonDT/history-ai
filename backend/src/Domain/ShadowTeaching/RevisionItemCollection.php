<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class RevisionItemCollection
{
    /** @param list<RevisionItem> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<RevisionItem> */
    public function all(): array
    {
        return $this->items;
    }
}

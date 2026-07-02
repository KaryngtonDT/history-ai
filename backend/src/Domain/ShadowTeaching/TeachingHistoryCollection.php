<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingHistoryCollection
{
    /** @param list<TeachingSessionRecord> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<TeachingSessionRecord> */
    public function all(): array
    {
        return $this->items;
    }

    public function append(TeachingSessionRecord $record): self
    {
        return new self([...$this->items, $record]);
    }
}

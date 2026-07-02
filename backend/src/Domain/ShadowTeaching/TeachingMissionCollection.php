<?php

declare(strict_types=1);

namespace App\Domain\ShadowTeaching;

final readonly class TeachingMissionCollection
{
    /** @param list<TeachingMission> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<TeachingMission> */
    public function all(): array
    {
        return $this->items;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Map;

final readonly class HistoricalPlaceCollection
{
    /** @var list<HistoricalPlace> */
    private array $places;

    /**
     * @param list<HistoricalPlace> $places
     */
    public function __construct(array $places)
    {
        $this->places = array_values($places);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<HistoricalPlace>
     */
    public function places(): array
    {
        return $this->places;
    }

    public function count(): int
    {
        return count($this->places);
    }

    public function isEmpty(): bool
    {
        return [] === $this->places;
    }
}

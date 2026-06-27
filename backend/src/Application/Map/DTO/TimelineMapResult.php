<?php

declare(strict_types=1);

namespace App\Application\Map\DTO;

use App\Domain\Map\HistoricalPlace;
use App\Domain\Map\HistoricalPlaceCollection;

final readonly class TimelineMapResult
{
    /**
     * @param list<HistoricalPlaceResult> $places
     */
    public function __construct(
        public array $places,
    ) {
    }

    public static function fromDomain(HistoricalPlaceCollection $collection): self
    {
        return new self(
            places: array_map(
                static fn (HistoricalPlace $place): HistoricalPlaceResult => HistoricalPlaceResult::fromDomain(
                    $place,
                ),
                $collection->places(),
            ),
        );
    }
}

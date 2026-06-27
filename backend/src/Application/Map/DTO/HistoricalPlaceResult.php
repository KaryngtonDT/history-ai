<?php

declare(strict_types=1);

namespace App\Application\Map\DTO;

use App\Domain\Map\HistoricalPlace;

final readonly class HistoricalPlaceResult
{
    public function __construct(
        public string $name,
        public CoordinatesResult $coordinates,
        public ?string $description,
    ) {
    }

    public static function fromDomain(HistoricalPlace $place): self
    {
        return new self(
            name: $place->name()->value(),
            coordinates: CoordinatesResult::fromDomain($place->coordinates()),
            description: $place->description(),
        );
    }
}

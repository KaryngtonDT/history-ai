<?php

declare(strict_types=1);

namespace App\Application\Map\DTO;

use App\Domain\Map\Coordinates;

final readonly class CoordinatesResult
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }

    public static function fromDomain(Coordinates $coordinates): self
    {
        return new self(
            latitude: $coordinates->latitude(),
            longitude: $coordinates->longitude(),
        );
    }
}

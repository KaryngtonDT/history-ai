<?php

declare(strict_types=1);

namespace App\Domain\Map;

use App\Domain\Map\Exception\InvalidCoordinatesException;

final readonly class Coordinates
{
    public function __construct(
        private float $latitude,
        private float $longitude,
    ) {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new InvalidCoordinatesException(
                sprintf('Latitude must be between -90 and 90, got %s.', $latitude),
            );
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new InvalidCoordinatesException(
                sprintf('Longitude must be between -180 and 180, got %s.', $longitude),
            );
        }
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    public function equals(self $other): bool
    {
        return $this->latitude === $other->latitude
            && $this->longitude === $other->longitude;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Map;

final readonly class HistoricalPlace
{
    private ?string $description;

    public function __construct(
        private PlaceName $name,
        private Coordinates $coordinates,
        ?string $description = null,
    ) {
        if (null !== $description) {
            $description = trim($description);
        }

        $this->description = (null === $description || '' === $description) ? null : $description;
    }

    public function name(): PlaceName
    {
        return $this->name;
    }

    public function coordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}

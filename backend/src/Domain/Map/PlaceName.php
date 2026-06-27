<?php

declare(strict_types=1);

namespace App\Domain\Map;

final readonly class PlaceName
{
    public string $value;

    public function __construct(string $raw)
    {
        $value = trim($raw);

        if ('' === $value) {
            throw new Exception\InvalidPlaceNameException('Place name cannot be empty.');
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

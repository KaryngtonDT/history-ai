<?php

declare(strict_types=1);

namespace App\Domain\Collection;

final readonly class CollectionDescription
{
    public function __construct(public string $value)
    {
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isEmpty(): bool
    {
        return '' === trim($this->value);
    }
}

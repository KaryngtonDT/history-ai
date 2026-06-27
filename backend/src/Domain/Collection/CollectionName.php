<?php

declare(strict_types=1);

namespace App\Domain\Collection;

use App\Domain\Collection\Exception\InvalidCollectionNameException;

final readonly class CollectionName
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new InvalidCollectionNameException('Collection name cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Content;

use App\Domain\Content\Exception\InvalidContentTitleException;

final readonly class ContentTitle
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new InvalidContentTitleException('Content title cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

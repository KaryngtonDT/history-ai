<?php

declare(strict_types=1);

namespace App\Domain\Library;

use App\Domain\Library\Exception\InvalidLibraryItemTitleException;

final readonly class LibraryItemTitle
{
    public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw new InvalidLibraryItemTitleException('Library item title cannot be empty.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

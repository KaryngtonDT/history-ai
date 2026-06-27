<?php

declare(strict_types=1);

namespace App\Domain\Search;

use App\Domain\Search\Exception\InvalidSearchQueryException;

final readonly class SearchQuery
{
    private const MAX_LENGTH = 255;

    public string $value;

    public function __construct(string $raw)
    {
        $value = trim($raw);

        if ('' === $value) {
            throw new InvalidSearchQueryException('Search query cannot be empty.');
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw new InvalidSearchQueryException(
                sprintf('Search query cannot exceed %d characters.', self::MAX_LENGTH),
            );
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

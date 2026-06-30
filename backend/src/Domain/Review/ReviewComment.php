<?php

declare(strict_types=1);

namespace App\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;

final readonly class ReviewComment
{
    private const int MAX_LENGTH = 2000;

    public function __construct(private string $value)
    {
        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidReviewException(sprintf(
                'Review comment must not exceed %d characters.',
                self::MAX_LENGTH,
            ));
        }
    }

    public static function empty(): self
    {
        return new self('');
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return '' === $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

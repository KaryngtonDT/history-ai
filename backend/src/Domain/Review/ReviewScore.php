<?php

declare(strict_types=1);

namespace App\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;

final readonly class ReviewScore
{
    private const int MIN = 1;

    private const int MAX = 5;

    public function __construct(private int $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new InvalidReviewException(sprintf(
                'Review score must be between %d and %d.',
                self::MIN,
                self::MAX,
            ));
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function averageWith(self $other): self
    {
        return new self((int) round(($this->value + $other->value) / 2));
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

use App\Domain\Recommendation\Exception\InvalidRecommendationScoreException;

final readonly class RecommendationScore
{
    public function __construct(private int $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidRecommendationScoreException(
                sprintf('Recommendation score must be between 0 and 100, got %d.', $value),
            );
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

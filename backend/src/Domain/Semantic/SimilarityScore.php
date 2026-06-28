<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Semantic\Exception\InvalidSimilarityScoreException;

final readonly class SimilarityScore
{
    public function __construct(private float $value)
    {
        if ($value < 0.0 || $value > 1.0) {
            throw new InvalidSimilarityScoreException(
                sprintf('Similarity score must be between 0.0 and 1.0, got %.4f.', $value),
            );
        }
    }

    public function value(): float
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

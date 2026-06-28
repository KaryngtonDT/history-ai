<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

final readonly class ScoredRecommendation
{
    public function __construct(
        private RecommendedArtifact $recommendation,
        private RecommendationScore $score,
    ) {
    }

    public function recommendation(): RecommendedArtifact
    {
        return $this->recommendation;
    }

    public function score(): RecommendationScore
    {
        return $this->score;
    }
}

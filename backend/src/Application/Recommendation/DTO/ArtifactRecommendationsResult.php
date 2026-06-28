<?php

declare(strict_types=1);

namespace App\Application\Recommendation\DTO;

use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\RecommendedArtifactCollection;
use App\Domain\Recommendation\ScoredRecommendation;
use App\Domain\Recommendation\ScoredRecommendationCollection;

final readonly class ArtifactRecommendationsResult
{
    /**
     * @param list<RecommendedArtifactResult> $recommendations
     */
    public function __construct(
        public array $recommendations,
    ) {
    }

    public static function fromScoredDomain(ScoredRecommendationCollection $collection): self
    {
        return new self(
            recommendations: array_map(
                static fn (ScoredRecommendation $scored): RecommendedArtifactResult => RecommendedArtifactResult::fromDomain(
                    $scored->recommendation(),
                ),
                $collection->recommendations(),
            ),
        );
    }
}

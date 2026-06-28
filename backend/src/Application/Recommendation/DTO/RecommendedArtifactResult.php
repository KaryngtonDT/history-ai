<?php

declare(strict_types=1);

namespace App\Application\Recommendation\DTO;

use App\Domain\Recommendation\RecommendedArtifact;
use App\Domain\Recommendation\ScoredRecommendation;

final readonly class RecommendedArtifactResult
{
    public function __construct(
        public string $artifactId,
        public string $type,
        public string $title,
        public string $reason,
        public ?int $score = null,
    ) {
    }

    public static function fromDomain(RecommendedArtifact $recommendation): self
    {
        return new self(
            artifactId: $recommendation->artifactId()->value,
            type: $recommendation->artifactType()->value,
            title: $recommendation->title(),
            reason: $recommendation->reason()->value,
        );
    }

    public static function fromScoredDomain(ScoredRecommendation $scored): self
    {
        $recommendation = $scored->recommendation();

        return new self(
            artifactId: $recommendation->artifactId()->value,
            type: $recommendation->artifactType()->value,
            title: $recommendation->title(),
            reason: $recommendation->reason()->value,
            score: $scored->score()->value(),
        );
    }
}

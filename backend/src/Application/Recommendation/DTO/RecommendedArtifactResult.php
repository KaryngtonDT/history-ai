<?php

declare(strict_types=1);

namespace App\Application\Recommendation\DTO;

use App\Domain\Recommendation\RecommendedArtifact;

final readonly class RecommendedArtifactResult
{
    public function __construct(
        public string $artifactId,
        public string $type,
        public string $title,
        public string $reason,
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
}

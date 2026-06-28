<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class VectorSearchResult
{
    public function __construct(
        private VectorDocument $document,
        private SimilarityScore $score,
    ) {
    }

    public function document(): VectorDocument
    {
        return $this->document;
    }

    public function score(): SimilarityScore
    {
        return $this->score;
    }
}

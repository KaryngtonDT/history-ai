<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class RetrievedChunk
{
    public function __construct(
        private Chunk $chunk,
        private SimilarityScore $score,
    ) {
    }

    public function chunk(): Chunk
    {
        return $this->chunk;
    }

    public function score(): SimilarityScore
    {
        return $this->score;
    }
}

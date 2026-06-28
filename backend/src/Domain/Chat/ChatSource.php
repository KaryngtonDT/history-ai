<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;

final readonly class ChatSource
{
    public function __construct(
        private ArtifactId $artifactId,
        private ChunkId $chunkId,
        private string $text,
        private SimilarityScore $score,
    ) {
    }

    public static function fromRetrievedChunk(RetrievedChunk $retrievedChunk): self
    {
        return new self(
            $retrievedChunk->chunk()->artifactId(),
            $retrievedChunk->chunk()->id(),
            $retrievedChunk->chunk()->text()->value(),
            $retrievedChunk->score(),
        );
    }

    public function artifactId(): ArtifactId
    {
        return $this->artifactId;
    }

    public function chunkId(): ChunkId
    {
        return $this->chunkId;
    }

    public function text(): string
    {
        return $this->text;
    }

    public function score(): SimilarityScore
    {
        return $this->score;
    }

    public function equals(self $other): bool
    {
        return $this->artifactId->equals($other->artifactId)
            && $this->chunkId->equals($other->chunkId)
            && $this->text === $other->text
            && $this->score->equals($other->score);
    }
}

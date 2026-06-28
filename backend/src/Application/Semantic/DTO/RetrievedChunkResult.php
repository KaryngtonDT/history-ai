<?php

declare(strict_types=1);

namespace App\Application\Semantic\DTO;

use App\Domain\Semantic\RetrievedChunk;

final readonly class RetrievedChunkResult
{
    public function __construct(
        public string $artifactId,
        public string $chunkId,
        public int $position,
        public string $text,
        public float $score,
    ) {
    }

    public static function fromDomain(RetrievedChunk $retrievedChunk): self
    {
        $chunk = $retrievedChunk->chunk();

        return new self(
            artifactId: $chunk->artifactId()->value,
            chunkId: $chunk->id()->value,
            position: $chunk->position()->value(),
            text: $chunk->text()->value(),
            score: $retrievedChunk->score()->value(),
        );
    }
}

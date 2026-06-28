<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

final readonly class EmbeddedChunk
{
    public function __construct(
        private Chunk $chunk,
        private EmbeddingVector $vector,
    ) {
    }

    public function chunk(): Chunk
    {
        return $this->chunk;
    }

    public function vector(): EmbeddingVector
    {
        return $this->vector;
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddedChunkCollection;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\EmbeddingProviderInterface;

final class DeterministicEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    public function __construct(
        private readonly EmbeddingProviderInterface $provider,
    ) {
    }

    public function generate(ChunkCollection $chunks): EmbeddedChunkCollection
    {
        if ($chunks->isEmpty()) {
            return EmbeddedChunkCollection::empty();
        }

        /** @var list<EmbeddedChunk> $embeddedChunks */
        $embeddedChunks = [];

        foreach ($chunks->chunks() as $chunk) {
            $embeddedChunks[] = new EmbeddedChunk(
                $chunk,
                $this->provider->generateEmbedding($chunk->text()),
            );
        }

        return new EmbeddedChunkCollection($embeddedChunks);
    }
}

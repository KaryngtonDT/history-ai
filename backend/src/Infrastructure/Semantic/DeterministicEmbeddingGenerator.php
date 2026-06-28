<?php

declare(strict_types=1);

namespace App\Infrastructure\Semantic;

use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddedChunkCollection;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\EmbeddingVector;

final class DeterministicEmbeddingGenerator implements EmbeddingGeneratorInterface
{
    private const int VECTOR_DIMENSION = 8;

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
                $this->vectorForChunk($chunk),
            );
        }

        return new EmbeddedChunkCollection($embeddedChunks);
    }

    private function vectorForChunk(Chunk $chunk): EmbeddingVector
    {
        $hash = hash('sha256', $chunk->text()->value());
        /** @var list<float> $values */
        $values = [];

        for ($index = 0; $index < self::VECTOR_DIMENSION; ++$index) {
            $hex = substr($hash, $index * 2, 2);
            $values[] = ((float) hexdec($hex) / 255.0) * 2.0 - 1.0;
        }

        return new EmbeddingVector($values);
    }
}

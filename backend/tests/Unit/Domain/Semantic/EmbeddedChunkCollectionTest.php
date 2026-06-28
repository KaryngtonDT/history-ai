<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddedChunkCollection;
use App\Domain\Semantic\EmbeddingVector;
use PHPUnit\Framework\TestCase;

final class EmbeddedChunkCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = EmbeddedChunkCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->embeddedChunks());
    }

    public function testPreservesEmbeddedChunkOrder(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createEmbeddedChunk($artifactId, 0, 'First chunk', [0.1, 0.2]);
        $second = $this->createEmbeddedChunk($artifactId, 1, 'Second chunk', [0.3, 0.4]);

        $collection = new EmbeddedChunkCollection([$first, $second]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            ['First chunk', 'Second chunk'],
            array_map(
                static fn (EmbeddedChunk $embeddedChunk): string => $embeddedChunk->chunk()->text()->value(),
                $collection->embeddedChunks(),
            ),
        );
    }

    /**
     * @param list<float> $vectorValues
     */
    private function createEmbeddedChunk(
        ArtifactId $artifactId,
        int $position,
        string $text,
        array $vectorValues,
    ): EmbeddedChunk {
        $chunkPosition = new ChunkPosition($position);

        return new EmbeddedChunk(
            new Chunk(
                ChunkId::derive($artifactId, $chunkPosition),
                $artifactId,
                ChunkText::fromString($text),
                $chunkPosition,
            ),
            new EmbeddingVector($vectorValues),
        );
    }
}

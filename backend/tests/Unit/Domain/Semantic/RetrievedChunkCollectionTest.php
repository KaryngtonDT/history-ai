<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\RetrievedChunkCollection;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class RetrievedChunkCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = RetrievedChunkCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->retrievedChunks());
    }

    public function testPreservesRetrievedChunkOrder(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createRetrievedChunk($artifactId, 0, 'First chunk', 0.9);
        $second = $this->createRetrievedChunk($artifactId, 1, 'Second chunk', 0.7);

        $collection = new RetrievedChunkCollection([$first, $second]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            ['First chunk', 'Second chunk'],
            array_map(
                static fn (RetrievedChunk $retrievedChunk): string => $retrievedChunk->chunk()->text()->value(),
                $collection->retrievedChunks(),
            ),
        );
    }

    private function createRetrievedChunk(
        ArtifactId $artifactId,
        int $position,
        string $text,
        float $score,
    ): RetrievedChunk {
        $chunkPosition = new ChunkPosition($position);

        return new RetrievedChunk(
            new Chunk(
                ChunkId::derive($artifactId, $chunkPosition),
                $artifactId,
                ChunkText::fromString($text),
                $chunkPosition,
            ),
            new SimilarityScore($score),
        );
    }
}

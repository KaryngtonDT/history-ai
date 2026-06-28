<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use PHPUnit\Framework\TestCase;

final class ChunkCollectionTest extends TestCase
{
    public function testEmptyCollectionHasNoChunks(): void
    {
        $collection = ChunkCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->chunks());
    }

    public function testPreservesChunkOrder(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createChunk($artifactId, 0, 'First chunk');
        $second = $this->createChunk($artifactId, 1, 'Second chunk');

        $collection = new ChunkCollection([$first, $second]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            ['First chunk', 'Second chunk'],
            array_map(
                static fn (Chunk $chunk): string => $chunk->text()->value(),
                $collection->chunks(),
            ),
        );
    }

    private function createChunk(ArtifactId $artifactId, int $position, string $text): Chunk
    {
        $chunkPosition = new ChunkPosition($position);

        return new Chunk(
            ChunkId::derive($artifactId, $chunkPosition),
            $artifactId,
            ChunkText::fromString($text),
            $chunkPosition,
        );
    }
}

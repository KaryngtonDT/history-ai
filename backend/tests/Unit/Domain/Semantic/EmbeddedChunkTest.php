<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddingVector;
use PHPUnit\Framework\TestCase;

final class EmbeddedChunkTest extends TestCase
{
    public function testContainsChunkAndVector(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );
        $vector = new EmbeddingVector([0.12, 0.34, 0.56]);

        $embeddedChunk = new EmbeddedChunk($chunk, $vector);

        self::assertSame($chunk, $embeddedChunk->chunk());
        self::assertSame($vector, $embeddedChunk->vector());
        self::assertSame('## Roman Republic', $embeddedChunk->chunk()->text()->value());
        self::assertSame([0.12, 0.34, 0.56], $embeddedChunk->vector()->values());
    }
}

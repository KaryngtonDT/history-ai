<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatSource;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatSourceTest extends TestCase
{
    public function testFromRetrievedChunkMapsFields(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('Roman Republic overview'),
            $position,
        );
        $retrievedChunk = new RetrievedChunk($chunk, new SimilarityScore(0.87));

        $source = ChatSource::fromRetrievedChunk($retrievedChunk);

        self::assertTrue($source->artifactId()->equals($artifactId));
        self::assertTrue($source->chunkId()->equals($chunk->id()));
        self::assertSame('Roman Republic overview', $source->text());
        self::assertSame(0.87, $source->score()->value());
    }

    public function testEqualsComparesAllFields(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $position = new ChunkPosition(1);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('Timeline excerpt'),
            $position,
        );
        $retrievedChunk = new RetrievedChunk($chunk, new SimilarityScore(0.75));

        $first = ChatSource::fromRetrievedChunk($retrievedChunk);
        $second = ChatSource::fromRetrievedChunk($retrievedChunk);

        self::assertTrue($first->equals($second));
    }
}

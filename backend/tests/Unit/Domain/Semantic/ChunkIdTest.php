<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\Exception\InvalidChunkException;
use PHPUnit\Framework\TestCase;

final class ChunkIdTest extends TestCase
{
    public function testDerivesDeterministicIdFromArtifactAndPosition(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);

        $first = ChunkId::derive($artifactId, $position);
        $second = ChunkId::derive($artifactId, $position);

        self::assertTrue($first->equals($second));
    }

    public function testDifferentPositionsProduceDifferentIds(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');

        $first = ChunkId::derive($artifactId, new ChunkPosition(0));
        $second = ChunkId::derive($artifactId, new ChunkPosition(1));

        self::assertFalse($first->equals($second));
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidChunkException::class);

        new ChunkId('not-a-uuid');
    }

    public function testEqualsComparesValue(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $id = ChunkId::derive($artifactId, new ChunkPosition(0));

        self::assertTrue($id->equals(ChunkId::derive($artifactId, new ChunkPosition(0))));
        self::assertFalse($id->equals(ChunkId::derive($artifactId, new ChunkPosition(1))));
    }
}

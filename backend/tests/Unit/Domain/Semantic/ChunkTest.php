<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use PHPUnit\Framework\TestCase;

final class ChunkTest extends TestCase
{
    public function testExposesIdArtifactIdTextAndPosition(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );

        self::assertSame($artifactId, $chunk->artifactId());
        self::assertSame('## Roman Republic', $chunk->text()->value());
        self::assertSame(0, $chunk->position()->value());
        self::assertTrue($chunk->id()->equals(ChunkId::derive($artifactId, $position)));
    }
}

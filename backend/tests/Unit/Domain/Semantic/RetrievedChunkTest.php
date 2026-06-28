<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class RetrievedChunkTest extends TestCase
{
    public function testContainsChunkAndScore(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );
        $score = new SimilarityScore(0.92);

        $retrievedChunk = new RetrievedChunk($chunk, $score);

        self::assertSame($chunk, $retrievedChunk->chunk());
        self::assertSame($score, $retrievedChunk->score());
    }
}

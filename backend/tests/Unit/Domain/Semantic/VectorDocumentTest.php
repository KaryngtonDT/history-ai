<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\VectorDocument;
use PHPUnit\Framework\TestCase;

final class VectorDocumentTest extends TestCase
{
    public function testContainsChunkAndEmbeddingVector(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );
        $vector = new EmbeddingVector([0.1, 0.2, 0.3]);

        $document = new VectorDocument($chunk, $vector);

        self::assertSame($chunk, $document->chunk());
        self::assertSame($vector, $document->vector());
    }
}

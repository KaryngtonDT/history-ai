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
use App\Domain\Semantic\VectorDocumentCollection;
use PHPUnit\Framework\TestCase;

final class VectorDocumentCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = VectorDocumentCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->documents());
    }

    public function testPreservesDocumentOrder(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createVectorDocument($artifactId, 0, 'First chunk', [0.1, 0.2]);
        $second = $this->createVectorDocument($artifactId, 1, 'Second chunk', [0.3, 0.4]);

        $collection = new VectorDocumentCollection([$first, $second]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            ['First chunk', 'Second chunk'],
            array_map(
                static fn (VectorDocument $document): string => $document->chunk()->text()->value(),
                $collection->documents(),
            ),
        );
    }

    /**
     * @param list<float> $vectorValues
     */
    private function createVectorDocument(
        ArtifactId $artifactId,
        int $position,
        string $text,
        array $vectorValues,
    ): VectorDocument {
        $chunkPosition = new ChunkPosition($position);

        return new VectorDocument(
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

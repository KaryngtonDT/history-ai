<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\SimilarityScore;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorSearchResult;
use App\Domain\Semantic\VectorSearchResultCollection;
use PHPUnit\Framework\TestCase;

final class VectorSearchResultCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = VectorSearchResultCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->results());
    }

    public function testPreservesResultOrder(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createVectorSearchResult($artifactId, 0, 'First chunk', 0.91);
        $second = $this->createVectorSearchResult($artifactId, 1, 'Second chunk', 0.62);

        $collection = new VectorSearchResultCollection([$first, $second]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            ['First chunk', 'Second chunk'],
            array_map(
                static fn (VectorSearchResult $result): string => $result->document()->chunk()->text()->value(),
                $collection->results(),
            ),
        );
        self::assertSame(
            [0.91, 0.62],
            array_map(
                static fn (VectorSearchResult $result): float => $result->score()->value(),
                $collection->results(),
            ),
        );
    }

    private function createVectorSearchResult(
        ArtifactId $artifactId,
        int $position,
        string $text,
        float $score,
    ): VectorSearchResult {
        $chunkPosition = new ChunkPosition($position);

        return new VectorSearchResult(
            new VectorDocument(
                new Chunk(
                    ChunkId::derive($artifactId, $chunkPosition),
                    $artifactId,
                    ChunkText::fromString($text),
                    $chunkPosition,
                ),
                new EmbeddingVector([0.1, 0.2]),
            ),
            new SimilarityScore($score),
        );
    }
}

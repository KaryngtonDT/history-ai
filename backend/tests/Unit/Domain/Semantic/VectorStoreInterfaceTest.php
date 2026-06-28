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
use App\Domain\Semantic\VectorSearchResultCollection;
use App\Domain\Semantic\VectorStoreInterface;
use PHPUnit\Framework\TestCase;

final class VectorStoreInterfaceTest extends TestCase
{
    public function testVectorStoreInterfaceDefinesIndexAndSearchMethods(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );
        $documents = new VectorDocumentCollection([
            new VectorDocument($chunk, new EmbeddingVector([0.1, 0.2])),
        ]);
        $query = new EmbeddingVector([0.1, 0.2]);
        $expectedResults = VectorSearchResultCollection::empty();

        $store = $this->createMock(VectorStoreInterface::class);
        $store
            ->expects(self::once())
            ->method('index')
            ->with($documents);
        $store
            ->expects(self::once())
            ->method('search')
            ->with($query, 5)
            ->willReturn($expectedResults);

        $store->index($documents);
        self::assertSame($expectedResults, $store->search($query));
    }
}

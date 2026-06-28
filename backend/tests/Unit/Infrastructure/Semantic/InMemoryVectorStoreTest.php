<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorDocumentCollection;
use App\Domain\Semantic\VectorSearchResult;
use App\Domain\Semantic\VectorStoreInterface;
use App\Infrastructure\Semantic\InMemoryVectorStore;
use PHPUnit\Framework\TestCase;

final class InMemoryVectorStoreTest extends TestCase
{
    private InMemoryVectorStore $store;

    protected function setUp(): void
    {
        $this->store = new InMemoryVectorStore();
    }

    public function testImplementsVectorStoreInterface(): void
    {
        self::assertInstanceOf(VectorStoreInterface::class, $this->store);
    }

    public function testEmptyStoreReturnsEmptyResults(): void
    {
        $results = $this->store->search(new EmbeddingVector([1.0, 0.0]));

        self::assertTrue($results->isEmpty());
        self::assertSame(0, $results->count());
    }

    public function testIndexedDocumentsAreSearchable(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $document = $this->createVectorDocument(
            $artifactId,
            0,
            'Roman Republic overview',
            [1.0, 0.0],
        );

        $this->store->index(new VectorDocumentCollection([$document]));

        $results = $this->store->search(new EmbeddingVector([1.0, 0.0]));

        self::assertSame(1, $results->count());
        self::assertSame($document, $results->results()[0]->document());
    }

    public function testReturnsTopKResults(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $documents = [];

        for ($index = 0; $index < 6; ++$index) {
            $documents[] = $this->createVectorDocument(
                $artifactId,
                $index,
                sprintf('Chunk %d', $index),
                [$index / 10.0, 1.0 - ($index / 10.0)],
            );
        }

        $this->store->index(new VectorDocumentCollection($documents));

        $results = $this->store->search(new EmbeddingVector([1.0, 0.0]), 3);

        self::assertSame(3, $results->count());
    }

    public function testSortsBySimilarityDescending(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        $bestMatch = $this->createVectorDocument($artifactId, 0, 'Best match', [1.0, 0.0]);
        $mediumMatch = $this->createVectorDocument($artifactId, 1, 'Medium match', [0.7, 0.7]);
        $weakMatch = $this->createVectorDocument($artifactId, 2, 'Weak match', [0.0, 1.0]);

        $this->store->index(new VectorDocumentCollection([
            $weakMatch,
            $bestMatch,
            $mediumMatch,
        ]));

        $results = $this->store->search(new EmbeddingVector([1.0, 0.0]), 3);

        self::assertSame(
            ['Best match', 'Medium match', 'Weak match'],
            array_map(
                static fn (VectorSearchResult $result): string => $result->document()->chunk()->text()->value(),
                $results->results(),
            ),
        );
        self::assertGreaterThan(
            $results->results()[1]->score()->value(),
            $results->results()[0]->score()->value(),
        );
        self::assertGreaterThan(
            $results->results()[2]->score()->value(),
            $results->results()[1]->score()->value(),
        );
    }

    public function testPreservesStableOrderingForEqualSimilarity(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440004');
        $sharedVector = [1.0, 0.0];
        $first = $this->createVectorDocument($artifactId, 0, 'First equal match', $sharedVector);
        $second = $this->createVectorDocument($artifactId, 1, 'Second equal match', $sharedVector);
        $third = $this->createVectorDocument($artifactId, 2, 'Third equal match', $sharedVector);

        $this->store->index(new VectorDocumentCollection([$first, $second, $third]));

        $results = $this->store->search(new EmbeddingVector([1.0, 0.0]), 3);

        self::assertSame(
            ['First equal match', 'Second equal match', 'Third equal match'],
            array_map(
                static fn (VectorSearchResult $result): string => $result->document()->chunk()->text()->value(),
                $results->results(),
            ),
        );
        self::assertEqualsWithDelta(
            1.0,
            $results->results()[0]->score()->value(),
            0.0001,
        );
        self::assertEqualsWithDelta(
            $results->results()[0]->score()->value(),
            $results->results()[1]->score()->value(),
            0.0001,
        );
    }

    public function testLimitLessThanOrEqualToZeroReturnsEmptyResults(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440005');
        $this->store->index(new VectorDocumentCollection([
            $this->createVectorDocument($artifactId, 0, 'Indexed chunk', [1.0, 0.0]),
        ]));

        self::assertTrue($this->store->search(new EmbeddingVector([1.0, 0.0]), 0)->isEmpty());
        self::assertTrue($this->store->search(new EmbeddingVector([1.0, 0.0]), -1)->isEmpty());
    }

    public function testRepeatedIndexReplacesExistingDocuments(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440006');
        $firstBatch = new VectorDocumentCollection([
            $this->createVectorDocument($artifactId, 0, 'First batch chunk', [1.0, 0.0]),
        ]);
        $secondBatch = new VectorDocumentCollection([
            $this->createVectorDocument($artifactId, 0, 'Second batch chunk', [0.0, 1.0]),
        ]);

        $this->store->index($firstBatch);
        $firstResults = $this->store->search(new EmbeddingVector([1.0, 0.0]), 1);

        self::assertSame('First batch chunk', $firstResults->results()[0]->document()->chunk()->text()->value());

        $this->store->index($secondBatch);
        $secondResults = $this->store->search(new EmbeddingVector([1.0, 0.0]), 5);

        self::assertSame(1, $secondResults->count());
        self::assertSame(
            'Second batch chunk',
            $secondResults->results()[0]->document()->chunk()->text()->value(),
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

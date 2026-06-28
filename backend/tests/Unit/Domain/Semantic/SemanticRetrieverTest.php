<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddedChunk;
use App\Domain\Semantic\EmbeddedChunkCollection;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\EmbeddingVector;
use App\Domain\Semantic\SemanticQuery;
use App\Domain\Semantic\SemanticRetriever;
use App\Domain\Semantic\VectorDocument;
use App\Domain\Semantic\VectorDocumentCollection;
use App\Domain\Semantic\VectorSearchResultCollection;
use App\Domain\Semantic\VectorStoreInterface;
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use App\Infrastructure\Semantic\InMemoryVectorStore;
use PHPUnit\Framework\TestCase;

final class SemanticRetrieverTest extends TestCase
{
    private InMemoryVectorStore $vectorStore;

    private SemanticRetriever $retriever;

    private DeterministicEmbeddingGenerator $embeddingGenerator;

    protected function setUp(): void
    {
        $this->vectorStore = new InMemoryVectorStore();
        $this->retriever = new SemanticRetriever($this->vectorStore);
        $this->embeddingGenerator = new DeterministicEmbeddingGenerator(
            new DeterministicEmbeddingProvider(),
        );
    }

    public function testEmptyVectorStoreReturnsEmptyCollection(): void
    {
        $result = $this->retriever->retrieve(
            new SemanticQuery('Roman Republic'),
            $this->embeddingGenerator,
        );

        self::assertTrue($result->isEmpty());
    }

    public function testCallsVectorStoreSearchWithQueryEmbedding(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $chunk = $this->createChunk($artifactId, 0, 'Roman Republic overview');
        $queryVector = new EmbeddingVector([1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0]);
        $query = new SemanticQuery('Roman Republic overview');

        $generator = $this->createMock(EmbeddingGeneratorInterface::class);
        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(self::callback(static fn (ChunkCollection $collection): bool => 1 === $collection->count()))
            ->willReturn(new EmbeddedChunkCollection([
                new EmbeddedChunk($chunk, $queryVector),
            ]));

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore
            ->expects(self::once())
            ->method('search')
            ->with($queryVector, 5)
            ->willReturn(VectorSearchResultCollection::empty());

        $retriever = new SemanticRetriever($vectorStore);
        $retriever->retrieve($query, $generator);
    }

    public function testReturnsResultsSortedByDescendingSimilarity(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $embeddedChunks = new EmbeddedChunkCollection([
            $this->embedChunk($artifactId, 0, 'Greek history overview'),
            $this->embedChunk($artifactId, 1, 'Roman Republic overview'),
            $this->embedChunk($artifactId, 2, 'Roman Republic details'),
        ]);
        $queryText = 'Roman Republic overview';

        $this->indexEmbeddedChunks($embeddedChunks);

        $result = $this->retriever->retrieve(
            new SemanticQuery($queryText),
            $this->embeddingGenerator,
        );

        self::assertSame($queryText, $result->retrievedChunks()[0]->chunk()->text()->value());
        self::assertSame(1.0, $result->retrievedChunks()[0]->score()->value());

        $scores = array_map(
            static fn ($retrievedChunk): float => $retrievedChunk->score()->value(),
            $result->retrievedChunks(),
        );

        for ($index = 0; $index < count($scores) - 1; ++$index) {
            self::assertGreaterThanOrEqual($scores[$index + 1], $scores[$index]);
        }
    }

    public function testRespectsTopNLimit(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $embeddedChunks = new EmbeddedChunkCollection([
            $this->embedChunk($artifactId, 0, 'Chunk one'),
            $this->embedChunk($artifactId, 1, 'Chunk two'),
            $this->embedChunk($artifactId, 2, 'Chunk three'),
            $this->embedChunk($artifactId, 3, 'Chunk four'),
            $this->embedChunk($artifactId, 4, 'Chunk five'),
            $this->embedChunk($artifactId, 5, 'Chunk six'),
        ]);

        $this->indexEmbeddedChunks($embeddedChunks);

        $result = $this->retriever->retrieve(
            new SemanticQuery('Chunk query'),
            $this->embeddingGenerator,
            3,
        );

        self::assertSame(3, $result->count());
    }

    public function testUsesDefaultLimitOfFive(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        /** @var list<EmbeddedChunk> $embedded */
        $embedded = [];

        for ($index = 0; $index < 7; ++$index) {
            $embedded[] = $this->embedChunk($artifactId, $index, sprintf('Chunk %d', $index));
        }

        $this->indexEmbeddedChunks(new EmbeddedChunkCollection($embedded));

        $result = $this->retriever->retrieve(
            new SemanticQuery('Chunk query'),
            $this->embeddingGenerator,
        );

        self::assertSame(5, $result->count());
    }

    public function testPreservesStableOrderForEqualScores(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440004');
        $sharedText = 'Shared semantic text';
        $embeddedChunks = new EmbeddedChunkCollection([
            $this->embedChunk($artifactId, 0, $sharedText),
            $this->embedChunk($artifactId, 1, $sharedText),
            $this->embedChunk($artifactId, 2, 'Different semantic text'),
        ]);

        $this->indexEmbeddedChunks($embeddedChunks);

        $result = $this->retriever->retrieve(
            new SemanticQuery($sharedText),
            $this->embeddingGenerator,
        );

        self::assertSame(1.0, $result->retrievedChunks()[0]->score()->value());
        self::assertSame(1.0, $result->retrievedChunks()[1]->score()->value());
        self::assertSame(0, $result->retrievedChunks()[0]->chunk()->position()->value());
        self::assertSame(1, $result->retrievedChunks()[1]->chunk()->position()->value());
    }

    public function testProducesDeterministicResults(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440005');
        $embeddedChunks = new EmbeddedChunkCollection([
            $this->embedChunk($artifactId, 0, 'Alpha chunk'),
            $this->embedChunk($artifactId, 1, 'Beta chunk'),
        ]);
        $query = new SemanticQuery('Alpha chunk');

        $this->indexEmbeddedChunks($embeddedChunks);

        $firstRun = $this->retriever->retrieve($query, $this->embeddingGenerator);
        $this->indexEmbeddedChunks($embeddedChunks);
        $secondRun = $this->retriever->retrieve($query, $this->embeddingGenerator);

        self::assertSame(
            array_map(
                static fn ($retrievedChunk): string => $retrievedChunk->chunk()->text()->value(),
                $firstRun->retrievedChunks(),
            ),
            array_map(
                static fn ($retrievedChunk): string => $retrievedChunk->chunk()->text()->value(),
                $secondRun->retrievedChunks(),
            ),
        );
        self::assertSame(
            array_map(
                static fn ($retrievedChunk): float => $retrievedChunk->score()->value(),
                $firstRun->retrievedChunks(),
            ),
            array_map(
                static fn ($retrievedChunk): float => $retrievedChunk->score()->value(),
                $secondRun->retrievedChunks(),
            ),
        );
    }

    public function testUsesEmbeddingGeneratorInterfaceWithoutProviderSpecificDependency(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440006');
        $chunk = $this->createChunk($artifactId, 0, 'Interface chunk text');
        $sharedVector = new EmbeddingVector([1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0]);

        $this->vectorStore->index(new VectorDocumentCollection([
            new VectorDocument($chunk, $sharedVector),
        ]));

        $generator = $this->createMock(EmbeddingGeneratorInterface::class);
        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(self::callback(static fn (ChunkCollection $collection): bool => 1 === $collection->count()))
            ->willReturn(new EmbeddedChunkCollection([
                new EmbeddedChunk($chunk, $sharedVector),
            ]));

        $result = $this->retriever->retrieve(
            new SemanticQuery('Interface chunk text'),
            $generator,
        );

        self::assertSame(1, $result->count());
        self::assertSame(1.0, $result->retrievedChunks()[0]->score()->value());
    }

    private function indexEmbeddedChunks(EmbeddedChunkCollection $embeddedChunks): void
    {
        /** @var list<VectorDocument> $documents */
        $documents = array_map(
            static fn (EmbeddedChunk $embeddedChunk): VectorDocument => new VectorDocument(
                $embeddedChunk->chunk(),
                $embeddedChunk->vector(),
            ),
            $embeddedChunks->embeddedChunks(),
        );

        $this->vectorStore->index(new VectorDocumentCollection($documents));
    }

    private function embedChunk(ArtifactId $artifactId, int $position, string $text): EmbeddedChunk
    {
        $chunk = $this->createChunk($artifactId, $position, $text);

        return $this->embeddingGenerator
            ->generate(new ChunkCollection([$chunk]))
            ->embeddedChunks()[0];
    }

    private function createChunk(ArtifactId $artifactId, int $position, string $text): Chunk
    {
        $chunkPosition = new ChunkPosition($position);

        return new Chunk(
            ChunkId::derive($artifactId, $chunkPosition),
            $artifactId,
            ChunkText::fromString($text),
            $chunkPosition,
        );
    }
}

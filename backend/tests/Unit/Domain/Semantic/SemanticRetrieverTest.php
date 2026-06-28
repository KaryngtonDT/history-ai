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
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use PHPUnit\Framework\TestCase;

final class SemanticRetrieverTest extends TestCase
{
    private SemanticRetriever $retriever;

    private DeterministicEmbeddingGenerator $embeddingGenerator;

    protected function setUp(): void
    {
        $this->retriever = new SemanticRetriever();
        $this->embeddingGenerator = new DeterministicEmbeddingGenerator();
    }

    public function testEmptyEmbeddedChunksReturnsEmptyCollection(): void
    {
        $result = $this->retriever->retrieve(
            new SemanticQuery('Roman Republic'),
            EmbeddedChunkCollection::empty(),
            $this->embeddingGenerator,
        );

        self::assertTrue($result->isEmpty());
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

        $result = $this->retriever->retrieve(
            new SemanticQuery($queryText),
            $embeddedChunks,
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

        $result = $this->retriever->retrieve(
            new SemanticQuery('Chunk query'),
            $embeddedChunks,
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

        $result = $this->retriever->retrieve(
            new SemanticQuery('Chunk query'),
            new EmbeddedChunkCollection($embedded),
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

        $result = $this->retriever->retrieve(
            new SemanticQuery($sharedText),
            $embeddedChunks,
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

        $firstRun = $this->retriever->retrieve($query, $embeddedChunks, $this->embeddingGenerator);
        $secondRun = $this->retriever->retrieve($query, $embeddedChunks, $this->embeddingGenerator);

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
        $embeddedChunks = new EmbeddedChunkCollection([
            new EmbeddedChunk($chunk, new EmbeddingVector([1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0])),
        ]);

        $generator = $this->createMock(EmbeddingGeneratorInterface::class);
        $generator
            ->expects(self::once())
            ->method('generate')
            ->with(self::callback(static fn (ChunkCollection $collection): bool => 1 === $collection->count()))
            ->willReturn(new EmbeddedChunkCollection([
                new EmbeddedChunk(
                    $chunk,
                    new EmbeddingVector([1.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0]),
                ),
            ]));

        $result = $this->retriever->retrieve(
            new SemanticQuery('Interface chunk text'),
            $embeddedChunks,
            $generator,
        );

        self::assertSame(1, $result->count());
        self::assertSame(1.0, $result->retrievedChunks()[0]->score()->value());
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

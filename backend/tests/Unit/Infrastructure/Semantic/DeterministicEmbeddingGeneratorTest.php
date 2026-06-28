<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkCollection;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\EmbeddingGeneratorInterface;
use App\Domain\Semantic\EmbeddingProviderInterface;
use App\Domain\Semantic\EmbeddingVector;
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DeterministicEmbeddingGeneratorTest extends TestCase
{
    private DeterministicEmbeddingGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new DeterministicEmbeddingGenerator(
            new DeterministicEmbeddingProvider(),
        );
    }

    public function testImplementsEmbeddingGeneratorInterface(): void
    {
        self::assertInstanceOf(EmbeddingGeneratorInterface::class, $this->generator);
    }

    public function testEmptyChunkCollectionReturnsEmptyEmbeddedChunkCollection(): void
    {
        $result = $this->generator->generate(ChunkCollection::empty());

        self::assertTrue($result->isEmpty());
        self::assertSame(0, $result->count());
    }

    public function testGeneratesOneVectorPerChunk(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createChunk($artifactId, 0, 'First chunk');
        $second = $this->createChunk($artifactId, 1, 'Second chunk');
        $input = new ChunkCollection([$first, $second]);

        $result = $this->generator->generate($input);

        self::assertSame(2, $result->count());
        self::assertSame($first, $result->embeddedChunks()[0]->chunk());
        self::assertSame($second, $result->embeddedChunks()[1]->chunk());
    }

    public function testDelegatesVectorGenerationToProvider(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440005');
        $chunk = $this->createChunk($artifactId, 0, 'Delegated chunk');
        $expectedVector = new EmbeddingVector([0.1, -0.2, 0.3, -0.4, 0.5, -0.6, 0.7, -0.8]);

        $provider = $this->createMock(EmbeddingProviderInterface::class);
        $provider
            ->expects(self::once())
            ->method('generateEmbedding')
            ->with($chunk->text())
            ->willReturn($expectedVector);

        $generator = new DeterministicEmbeddingGenerator($provider);
        $result = $generator->generate(new ChunkCollection([$chunk]));

        self::assertSame($expectedVector, $result->embeddedChunks()[0]->vector());
    }

    public function testDoesNotContainHashingLogic(): void
    {
        $source = (new ReflectionClass(DeterministicEmbeddingGenerator::class))->getFileName();
        self::assertNotFalse($source);

        $contents = file_get_contents($source);
        self::assertNotFalse($contents);
        self::assertStringNotContainsString('sha256', $contents);
        self::assertStringNotContainsString('hash(', $contents);
    }

    public function testVectorDimensionIsFixed(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440002');
        $input = new ChunkCollection([
            $this->createChunk($artifactId, 0, 'Chunk with fixed dimension'),
        ]);

        $result = $this->generator->generate($input);

        self::assertSame(8, $result->embeddedChunks()[0]->vector()->dimension());
        self::assertCount(8, $result->embeddedChunks()[0]->vector()->values());
        foreach ($result->embeddedChunks()[0]->vector()->values() as $value) {
            self::assertIsFloat($value);
        }
    }

    public function testOutputIsDeterministic(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        $input = new ChunkCollection([
            $this->createChunk($artifactId, 0, 'Deterministic chunk text'),
        ]);

        $firstRun = $this->generator->generate($input);
        $secondRun = $this->generator->generate($input);

        self::assertTrue(
            $firstRun->embeddedChunks()[0]->vector()->equals(
                $secondRun->embeddedChunks()[0]->vector(),
            ),
        );
    }

    public function testDifferentTextProducesDifferentVector(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440004');
        $firstInput = new ChunkCollection([
            $this->createChunk($artifactId, 0, 'Roman Republic overview'),
        ]);
        $secondInput = new ChunkCollection([
            $this->createChunk($artifactId, 0, 'Roman Empire overview'),
        ]);

        $firstVector = $this->generator->generate($firstInput)->embeddedChunks()[0]->vector();
        $secondVector = $this->generator->generate($secondInput)->embeddedChunks()[0]->vector();

        self::assertFalse($firstVector->equals($secondVector));
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

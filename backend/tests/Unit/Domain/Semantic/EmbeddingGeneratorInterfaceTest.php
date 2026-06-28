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
use PHPUnit\Framework\TestCase;

final class EmbeddingGeneratorInterfaceTest extends TestCase
{
    public function testGeneratorInterfaceDefinesGenerateMethod(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $chunk = new Chunk(
            ChunkId::derive($artifactId, $position),
            $artifactId,
            ChunkText::fromString('## Roman Republic'),
            $position,
        );
        $input = new ChunkCollection([$chunk]);
        $expected = new EmbeddedChunkCollection([
            new EmbeddedChunk($chunk, new EmbeddingVector([0.1, 0.2])),
        ]);

        $generator = $this->createMock(EmbeddingGeneratorInterface::class);
        $generator
            ->expects(self::once())
            ->method('generate')
            ->with($input)
            ->willReturn($expected);

        self::assertSame($expected, $generator->generate($input));
    }
}

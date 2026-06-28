<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatSource;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatSourceCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = ChatSourceCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->sources());
    }

    public function testPreservesSourceOrder(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $first = $this->createSource($artifactId, 0, 'First excerpt', 0.9);
        $second = $this->createSource($artifactId, 1, 'Second excerpt', 0.7);

        $collection = new ChatSourceCollection([$first, $second]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            ['First excerpt', 'Second excerpt'],
            array_map(
                static fn (ChatSource $source): string => $source->text(),
                $collection->sources(),
            ),
        );
    }

    private function createSource(
        ArtifactId $artifactId,
        int $position,
        string $text,
        float $score,
    ): ChatSource {
        $chunkPosition = new ChunkPosition($position);

        return ChatSource::fromRetrievedChunk(
            new RetrievedChunk(
                new Chunk(
                    ChunkId::derive($artifactId, $chunkPosition),
                    $artifactId,
                    ChunkText::fromString($text),
                    $chunkPosition,
                ),
                new SimilarityScore($score),
            ),
        );
    }
}

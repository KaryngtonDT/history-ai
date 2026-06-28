<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatCitation;
use App\Domain\Chat\ChatCitationCollection;
use App\Domain\Chat\ChatSource;
use App\Domain\Chat\Exception\InvalidChatCitationException;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatCitationCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = ChatCitationCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->citations());
    }

    public function testPreservesCitationOrderAndNumbering(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $firstSource = $this->createSource($artifactId, 0, 'First excerpt', 0.9);
        $secondSource = $this->createSource($artifactId, 1, 'Second excerpt', 0.7);

        $collection = new ChatCitationCollection([
            new ChatCitation(1, $firstSource),
            new ChatCitation(2, $secondSource),
        ]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            [1, 2],
            array_map(
                static fn (ChatCitation $citation): int => $citation->number(),
                $collection->citations(),
            ),
        );
        self::assertSame(
            ['First excerpt', 'Second excerpt'],
            array_map(
                static fn (ChatCitation $citation): string => $citation->source()->text(),
                $collection->citations(),
            ),
        );
    }

    public function testRejectsNonSequentialNumbering(): void
    {
        $source = $this->createSource(
            new ArtifactId('550e8400-e29b-41d4-a716-446655440001'),
            0,
            'First excerpt',
            0.9,
        );

        $this->expectException(InvalidChatCitationException::class);

        new ChatCitationCollection([
            new ChatCitation(2, $source),
        ]);
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

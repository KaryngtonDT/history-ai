<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatCitation;
use App\Domain\Chat\ChatSource;
use App\Domain\Chat\Exception\InvalidChatCitationException;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatCitationTest extends TestCase
{
    public function testExposesNumberAndSource(): void
    {
        $source = $this->createSource('Summary excerpt', 0.9);
        $citation = new ChatCitation(1, $source);

        self::assertSame(1, $citation->number());
        self::assertSame($source, $citation->source());
    }

    public function testEqualsComparesNumberAndSource(): void
    {
        $source = $this->createSource('Summary excerpt', 0.9);
        $otherSource = $this->createSource('Timeline excerpt', 0.8);

        self::assertTrue((new ChatCitation(1, $source))->equals(new ChatCitation(1, $source)));
        self::assertFalse((new ChatCitation(1, $source))->equals(new ChatCitation(2, $source)));
        self::assertFalse((new ChatCitation(1, $source))->equals(new ChatCitation(1, $otherSource)));
    }

    public function testRejectsNonPositiveNumber(): void
    {
        $this->expectException(InvalidChatCitationException::class);

        new ChatCitation(0, $this->createSource('Summary excerpt', 0.9));
    }

    private function createSource(string $text, float $score): ChatSource
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440003');
        $position = new ChunkPosition(0);

        return ChatSource::fromRetrievedChunk(
            new RetrievedChunk(
                new Chunk(
                    ChunkId::derive($artifactId, $position),
                    $artifactId,
                    ChunkText::fromString($text),
                    $position,
                ),
                new SimilarityScore($score),
            ),
        );
    }
}

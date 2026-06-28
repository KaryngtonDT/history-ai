<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSource;
use App\Domain\Chat\ChatSourceCollection;
use App\Domain\Chat\Exception\InvalidChatQuestionException;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatResponseTest extends TestCase
{
    public function testExposesAnswerAndSources(): void
    {
        $sources = new ChatSourceCollection([
            $this->createSource('Summary excerpt', 0.9),
        ]);

        $response = new ChatResponse('Several factors contributed to the collapse.', $sources);

        self::assertSame('Several factors contributed to the collapse.', $response->answer());
        self::assertSame($sources, $response->sources());
    }

    public function testTrimsAnswer(): void
    {
        $response = new ChatResponse('  Plain text answer  ', ChatSourceCollection::empty());

        self::assertSame('Plain text answer', $response->answer());
    }

    public function testRejectsEmptyAnswer(): void
    {
        $this->expectException(InvalidChatQuestionException::class);

        new ChatResponse('   ', ChatSourceCollection::empty());
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

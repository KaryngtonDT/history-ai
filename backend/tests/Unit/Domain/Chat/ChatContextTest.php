<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatContext;
use App\Domain\Chat\ChatQuestion;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\RetrievedChunkCollection;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatContextTest extends TestCase
{
    public function testExposesQuestionAndRetrievedChunks(): void
    {
        $question = new ChatQuestion('Why did Rome fall?');
        $chunks = RetrievedChunkCollection::empty();
        $context = new ChatContext($question, $chunks);

        self::assertTrue($context->question()->equals($question));
        self::assertSame($chunks, $context->retrievedChunks());
    }

    public function testSourcesMapsRetrievedChunks(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $retrievedChunk = new RetrievedChunk(
            new Chunk(
                ChunkId::derive($artifactId, $position),
                $artifactId,
                ChunkText::fromString('Decline of the Western Empire'),
                $position,
            ),
            new SimilarityScore(0.82),
        );

        $context = new ChatContext(
            new ChatQuestion('Why did Rome fall?'),
            new RetrievedChunkCollection([$retrievedChunk]),
        );

        $sources = $context->sources();

        self::assertSame(1, $sources->count());
        self::assertSame('Decline of the Western Empire', $sources->sources()[0]->text());
        self::assertSame(0.82, $sources->sources()[0]->score()->value());
    }
}

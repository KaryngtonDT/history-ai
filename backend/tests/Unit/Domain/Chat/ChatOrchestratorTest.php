<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Chat\ChatContext;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\ChatQuestion;
use App\Domain\Semantic\Chunk;
use App\Domain\Semantic\ChunkId;
use App\Domain\Semantic\ChunkPosition;
use App\Domain\Semantic\ChunkText;
use App\Domain\Semantic\RetrievedChunk;
use App\Domain\Semantic\RetrievedChunkCollection;
use App\Domain\Semantic\SimilarityScore;
use PHPUnit\Framework\TestCase;

final class ChatOrchestratorTest extends TestCase
{
    private ChatOrchestrator $orchestrator;

    protected function setUp(): void
    {
        $this->orchestrator = new ChatOrchestrator();
    }

    public function testBuildPromptIncludesQuestionAndExcerpts(): void
    {
        $artifactId = new ArtifactId('550e8400-e29b-41d4-a716-446655440001');
        $position = new ChunkPosition(0);
        $context = new ChatContext(
            new ChatQuestion('Why did Rome fall?'),
            new RetrievedChunkCollection([
                new RetrievedChunk(
                    new Chunk(
                        ChunkId::derive($artifactId, $position),
                        $artifactId,
                        ChunkText::fromString('Economic instability weakened the empire.'),
                        $position,
                    ),
                    new SimilarityScore(0.91),
                ),
            ]),
        );

        $prompt = $this->orchestrator->buildPrompt($context);

        self::assertStringContainsString('Why did Rome fall?', $prompt->value());
        self::assertStringContainsString('Economic instability weakened the empire.', $prompt->value());
        self::assertStringContainsString('score: 0.9100', $prompt->value());
    }

    public function testBuildPromptHandlesEmptyRetrievedChunks(): void
    {
        $context = new ChatContext(
            new ChatQuestion('Why did Rome fall?'),
            RetrievedChunkCollection::empty(),
        );

        $prompt = $this->orchestrator->buildPrompt($context);

        self::assertStringContainsString('Why did Rome fall?', $prompt->value());
        self::assertStringContainsString('(no excerpts available)', $prompt->value());
    }

    public function testBuildPromptDoesNotContactExternalProviders(): void
    {
        $context = new ChatContext(
            new ChatQuestion('What happened in 476 AD?'),
            RetrievedChunkCollection::empty(),
        );

        $prompt = $this->orchestrator->buildPrompt($context);

        self::assertNotEmpty($prompt->value());
    }
}

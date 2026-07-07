<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Application\Semantic\Handlers\SearchSemanticChunksHandler;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Platform\CorrelationId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\SemanticRetriever;
use App\Infrastructure\Agent\SemanticSearchToolExecutor;
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use App\Infrastructure\Semantic\InMemoryVectorStore;
use App\Tests\Unit\Application\Platform\Support\FixedClock;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use App\Tests\Unit\Application\Platform\Support\RecordingPerformanceMetricsRecorder;
use App\Tests\Unit\Application\Platform\Support\RecordingPlatformLogger;
use PHPUnit\Framework\TestCase;

final class SemanticSearchToolExecutorTest extends TestCase
{
    public function testImplementsAgentToolExecutorInterface(): void
    {
        $handler = $this->createHandler($this->createStub(ArtifactRepositoryInterface::class));

        self::assertInstanceOf(
            AgentToolExecutorInterface::class,
            new SemanticSearchToolExecutor($handler),
        );
    }

    public function testExecuteCallsSearchSemanticChunksHandler(): void
    {
        $contentId = ContentId::generate();
        $queryText = "## Ancient Rome\n753 BC — Foundation of Rome";
        $artifacts = [
            $this->createArtifact(
                '550e8400-e29b-41d4-a716-446655440002',
                $contentId,
                ArtifactType::Summary,
                $queryText,
            ),
            $this->createArtifact(
                '550e8400-e29b-41d4-a716-446655440004',
                $contentId,
                ArtifactType::Timeline,
                "## Greek history\nClassical period overview",
            ),
            $this->createArtifact(
                '550e8400-e29b-41d4-a716-446655440006',
                $contentId,
                ArtifactType::Summary,
                "## Empire decline\nLate antiquity overview",
            ),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $execution = new AgentToolExecution(
            AgentTool::SemanticSearch,
            $queryText,
            $contentId->value,
        );

        $result = (new SemanticSearchToolExecutor($this->createHandler($repository)))->execute($execution);

        self::assertSame(AgentTool::SemanticSearch, $result->tool());
        self::assertSame('Semantic search found 3 relevant chunks.', $result->summary());
        self::assertSame(3, $result->metadata()['resultCount']);
        self::assertIsFloat($result->metadata()['topScore']);
        self::assertGreaterThanOrEqual(0.0, $result->metadata()['topScore']);
        self::assertLessThanOrEqual(1.0, $result->metadata()['topScore']);
    }

    public function testExecuteReturnsZeroResultSummaryAndMetadata(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([]);

        $execution = new AgentToolExecution(
            AgentTool::SemanticSearch,
            'What is Rome?',
            $contentId->value,
        );

        $result = (new SemanticSearchToolExecutor($this->createHandler($repository)))->execute($execution);

        self::assertSame('Semantic search found no relevant chunks.', $result->summary());
        self::assertSame(['resultCount' => 0], $result->metadata());
    }

    private function createHandler(ArtifactRepositoryInterface $repository): SearchSemanticChunksHandler
    {
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));
        $vectorStore = new InMemoryVectorStore();

        return new SearchSemanticChunksHandler(
            $repository,
            new Chunker(),
            new DeterministicEmbeddingGenerator(new DeterministicEmbeddingProvider()),
            $vectorStore,
            new SemanticRetriever($vectorStore),
            new RecordingPlatformLogger($contextProvider),
            new RecordingPerformanceMetricsRecorder(),
            new FixedClock(),
        );
    }

    private function createArtifact(
        string $id,
        ContentId $contentId,
        ArtifactType $type,
        string $content,
    ): Artifact {
        return Artifact::create(
            new ArtifactId($id),
            $contentId,
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString($content),
        );
    }
}

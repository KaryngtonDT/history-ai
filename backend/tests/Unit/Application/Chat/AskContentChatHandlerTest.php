<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat;

use App\Application\Chat\Commands\AskContentChatCommand;
use App\Application\Chat\ContentChatAnswerer;
use App\Application\Chat\Handlers\AskContentChatHandler;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\ChatProviderInterface;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatResponse;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\SemanticRetriever;
use App\Domain\Semantic\VectorDocumentCollection;
use App\Domain\Semantic\VectorSearchResultCollection;
use App\Domain\Semantic\VectorStoreInterface;
use App\Infrastructure\Chat\MockChatProvider;
use App\Infrastructure\Semantic\DeterministicEmbeddingGenerator;
use App\Infrastructure\Semantic\DeterministicEmbeddingProvider;
use App\Infrastructure\Semantic\InMemoryVectorStore;
use App\Tests\Unit\Application\Platform\Support\FixedClock;
use App\Tests\Unit\Application\Platform\Support\FixedRequestContextProvider;
use App\Tests\Unit\Application\Platform\Support\RecordingPerformanceMetricsRecorder;
use App\Tests\Unit\Application\Platform\Support\RecordingPlatformLogger;
use App\Domain\Platform\CorrelationId;
use PHPUnit\Framework\TestCase;

final class AskContentChatHandlerTest extends TestCase
{
    private function createContentChatAnswerer(
        ?VectorStoreInterface $vectorStore = null,
        ?ChatProviderInterface $chatProvider = null,
        ?RecordingPlatformLogger $platformLogger = null,
        ?RecordingPerformanceMetricsRecorder $metricsRecorder = null,
        ?FixedClock $clock = null,
    ): ContentChatAnswerer {
        $store = $vectorStore ?? new InMemoryVectorStore();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new ContentChatAnswerer(
            new Chunker(),
            new DeterministicEmbeddingGenerator(new DeterministicEmbeddingProvider()),
            $store,
            new SemanticRetriever($store),
            new ChatOrchestrator(),
            $chatProvider ?? new MockChatProvider(),
            $platformLogger ?? new RecordingPlatformLogger($contextProvider),
            $metricsRecorder ?? new RecordingPerformanceMetricsRecorder(),
            $clock ?? new FixedClock(),
        );
    }

    private function createHandler(
        ArtifactRepositoryInterface $repository,
        ?VectorStoreInterface $vectorStore = null,
        ?ChatProviderInterface $chatProvider = null,
        ?RecordingPlatformLogger $platformLogger = null,
        ?RecordingPerformanceMetricsRecorder $metricsRecorder = null,
        ?FixedClock $clock = null,
    ): AskContentChatHandler {
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new AskContentChatHandler(
            $repository,
            $this->createContentChatAnswerer(
                $vectorStore,
                $chatProvider,
                $platformLogger,
                $metricsRecorder,
                $clock,
            ),
            $platformLogger ?? new RecordingPlatformLogger($contextProvider),
        );
    }

    public function testReturnsMockAnswerWithEmptySourcesWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn([]);

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore->expects(self::never())->method('index');

        $result = $this->createHandler($repository, $vectorStore)
            ->__invoke(new AskContentChatCommand($contentId->value, 'Why did Rome fall?'));

        self::assertSame(MockChatProvider::MOCK_ANSWER, $result->answer);
        self::assertSame([], $result->sources);
        self::assertSame([], $result->citations);
    }

    public function testIndexesVectorDocumentsBeforeRetrieval(): void
    {
        $contentId = ContentId::generate();
        $artifacts = [
            $this->createArtifact(
                '550e8400-e29b-41d4-a716-446655440002',
                $contentId,
                ArtifactType::Summary,
                "## Ancient Rome\n753 BC — Foundation of Rome",
            ),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $vectorStore = $this->createMock(VectorStoreInterface::class);
        $vectorStore
            ->expects(self::once())
            ->method('index')
            ->with(self::callback(
                static fn (VectorDocumentCollection $documents): bool => 1 === $documents->count(),
            ));
        $vectorStore
            ->expects(self::once())
            ->method('search')
            ->willReturn(VectorSearchResultCollection::empty());

        $this->createHandler($repository, $vectorStore)
            ->__invoke(new AskContentChatCommand($contentId->value, 'Ancient Rome'));
    }

    public function testReturnsMockAnswerWithMappedSources(): void
    {
        $contentId = ContentId::generate();
        $summaryId = '550e8400-e29b-41d4-a716-446655440002';
        $queryText = "## Ancient Rome\n753 BC — Foundation of Rome";
        $artifacts = [
            $this->createArtifact(
                $summaryId,
                $contentId,
                ArtifactType::Summary,
                $queryText,
            ),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $result = $this->createHandler($repository)
            ->__invoke(new AskContentChatCommand($contentId->value, $queryText));

        self::assertSame('Mock answer based on retrieved context [1].', $result->answer);
        self::assertNotSame([], $result->sources);
        self::assertSame($summaryId, $result->sources[0]->artifactId);
        self::assertNotSame('', $result->sources[0]->chunkId);
        self::assertSame($queryText, $result->sources[0]->text);
        self::assertSame(1.0, $result->sources[0]->score);
        self::assertCount(1, $result->citations);
        self::assertSame(1, $result->citations[0]->number);
        self::assertSame($summaryId, $result->citations[0]->artifactId);
        self::assertSame($result->sources[0]->chunkId, $result->citations[0]->chunkId);
        self::assertSame(1.0, $result->citations[0]->score);
    }

    public function testDelegatesAnswerGenerationToChatProvider(): void
    {
        $contentId = ContentId::generate();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn([]);

        $chatProvider = $this->createMock(ChatProviderInterface::class);
        $chatProvider
            ->expects(self::once())
            ->method('answer')
            ->with(self::callback(static function (ChatRequest $request): bool {
                self::assertSame(
                    ChatProviderOptions::DEFAULT_TEMPERATURE,
                    $request->options()->temperature(),
                );
                self::assertSame(
                    ChatProviderOptions::DEFAULT_MAX_TOKENS,
                    $request->options()->maxTokens(),
                );
                self::assertNull($request->options()->model());

                return true;
            }))
            ->willReturn(new ChatResponse(
                MockChatProvider::MOCK_ANSWER,
                \App\Domain\Chat\ChatSourceCollection::empty(),
            ));

        $this->createHandler($repository, chatProvider: $chatProvider)
            ->__invoke(new AskContentChatCommand($contentId->value, 'Why did Rome fall?'));
    }

    public function testLogsLifecycleWithCorrelationId(): void
    {
        $contentId = ContentId::generate();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));
        $platformLogger = new RecordingPlatformLogger($contextProvider);

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn([]);

        $this->createHandler($repository, platformLogger: $platformLogger)
            ->__invoke(new AskContentChatCommand($contentId->value, 'Why did Rome fall?'));

        self::assertSame(
            ['request started', 'retrieval completed', 'provider completed', 'request completed'],
            $platformLogger->messages(),
        );
        self::assertSame(
            'c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d',
            $platformLogger->records()[0]['context']['correlationId'],
        );
    }

    public function testRecordsPerformanceMetricsOncePerRequest(): void
    {
        $contentId = ContentId::generate();
        $metricsRecorder = new RecordingPerformanceMetricsRecorder();
        $artifacts = [
            $this->createArtifact(
                '550e8400-e29b-41d4-a716-446655440002',
                $contentId,
                ArtifactType::Summary,
                "## Ancient Rome\n753 BC — Foundation of Rome",
            ),
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn($artifacts);

        $this->createHandler($repository, metricsRecorder: $metricsRecorder)
            ->__invoke(new AskContentChatCommand($contentId->value, 'Ancient Rome'));

        self::assertCount(1, $metricsRecorder->recordings());
        self::assertSame(
            ['chunking_ms', 'embedding_ms', 'vector_index_ms', 'retrieval_ms', 'provider_ms', 'total_ms'],
            $metricsRecorder->recordings()[0]->names(),
        );
    }

    public function testRecordsProviderAndTotalMetricsWhenNoArtifactsExist(): void
    {
        $contentId = ContentId::generate();
        $metricsRecorder = new RecordingPerformanceMetricsRecorder();

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn([]);

        $this->createHandler($repository, metricsRecorder: $metricsRecorder)
            ->__invoke(new AskContentChatCommand($contentId->value, 'Why did Rome fall?'));

        self::assertCount(1, $metricsRecorder->recordings());
        self::assertSame(['provider_ms', 'total_ms'], $metricsRecorder->recordings()[0]->names());
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

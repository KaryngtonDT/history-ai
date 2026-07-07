<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat;

use App\Application\Chat\Commands\AskContentChatStreamCommand;
use App\Application\Chat\Handlers\AskContentChatStreamHandler;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\ChatProviderOptions;
use App\Domain\Chat\ChatRequest;
use App\Domain\Chat\ChatStream;
use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\ChatStreamEventCollection;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\StreamingChatProviderInterface;
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

final class AskContentChatStreamHandlerTest extends TestCase
{
    private function createHandler(
        ArtifactRepositoryInterface $repository,
        ?VectorStoreInterface $vectorStore = null,
        ?StreamingChatProviderInterface $streamingChatProvider = null,
        ?RecordingPlatformLogger $platformLogger = null,
        ?RecordingPerformanceMetricsRecorder $metricsRecorder = null,
        ?FixedClock $clock = null,
    ): AskContentChatStreamHandler {
        $store = $vectorStore ?? new InMemoryVectorStore();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));
        $logger = $platformLogger ?? new RecordingPlatformLogger($contextProvider);
        $recorder = $metricsRecorder ?? new RecordingPerformanceMetricsRecorder();

        return new AskContentChatStreamHandler(
            $repository,
            new Chunker(),
            new DeterministicEmbeddingGenerator(new DeterministicEmbeddingProvider()),
            $store,
            new SemanticRetriever($store),
            new ChatOrchestrator(),
            $streamingChatProvider ?? new MockChatProvider(),
            $logger,
            $recorder,
            $clock ?? new FixedClock(),
        );
    }

    public function testReturnsMockStreamWithEmptySourcesWhenNoArtifactsExist(): void
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
            ->__invoke(new AskContentChatStreamCommand($contentId->value, 'Why did Rome fall?'));

        self::assertSame(
            [
                'Mock ',
                'answer ',
                'based ',
                'on ',
                'retrieved ',
                'context ',
                '.',
            ],
            array_map(
                static fn ($event): string => $event->text,
                $result->events,
            ),
        );
        self::assertSame(
            [0, 1, 2, 3, 4, 5, 6],
            array_map(
                static fn ($event): int => $event->index,
                $result->events,
            ),
        );
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
            ->__invoke(new AskContentChatStreamCommand($contentId->value, 'Ancient Rome'));
    }

    public function testReturnsMockStreamWithCitationTokenWhenSourcesExist(): void
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
        ];

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findByContentId')
            ->willReturn($artifacts);

        $result = $this->createHandler($repository)
            ->__invoke(new AskContentChatStreamCommand($contentId->value, $queryText));

        self::assertSame('[1].', $result->events[array_key_last($result->events)]->text);
    }

    public function testDelegatesStreamGenerationToStreamingChatProvider(): void
    {
        $contentId = ContentId::generate();
        $expectedStream = new ChatStream(new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('Mock ')),
        ]));

        $repository = $this->createStub(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn([]);

        $streamingChatProvider = $this->createMock(StreamingChatProviderInterface::class);
        $streamingChatProvider
            ->expects(self::once())
            ->method('stream')
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
            ->willReturn($expectedStream);

        $result = $this->createHandler($repository, streamingChatProvider: $streamingChatProvider)
            ->__invoke(new AskContentChatStreamCommand($contentId->value, 'Why did Rome fall?'));

        self::assertSame(1, count($result->events));
        self::assertSame(0, $result->events[0]->index);
        self::assertSame('Mock ', $result->events[0]->text);
    }

    public function testLogsLifecycleWithCorrelationId(): void
    {
        $contentId = ContentId::generate();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));
        $platformLogger = new RecordingPlatformLogger($contextProvider);

        $repository = $this->createStub(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn([]);

        $this->createHandler($repository, platformLogger: $platformLogger)
            ->__invoke(new AskContentChatStreamCommand($contentId->value, 'Why did Rome fall?'));

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

        $repository = $this->createStub(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn($artifacts);

        $this->createHandler($repository, metricsRecorder: $metricsRecorder)
            ->__invoke(new AskContentChatStreamCommand($contentId->value, 'Ancient Rome'));

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

        $repository = $this->createStub(ArtifactRepositoryInterface::class);
        $repository
            ->method('findByContentId')
            ->willReturn([]);

        $this->createHandler($repository, metricsRecorder: $metricsRecorder)
            ->__invoke(new AskContentChatStreamCommand($contentId->value, 'Why did Rome fall?'));

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

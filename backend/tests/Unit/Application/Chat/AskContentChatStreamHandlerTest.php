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
use PHPUnit\Framework\TestCase;

final class AskContentChatStreamHandlerTest extends TestCase
{
    private function createHandler(
        ArtifactRepositoryInterface $repository,
        ?VectorStoreInterface $vectorStore = null,
        ?StreamingChatProviderInterface $streamingChatProvider = null,
    ): AskContentChatStreamHandler {
        $store = $vectorStore ?? new InMemoryVectorStore();

        return new AskContentChatStreamHandler(
            $repository,
            new Chunker(),
            new DeterministicEmbeddingGenerator(new DeterministicEmbeddingProvider()),
            $store,
            new SemanticRetriever($store),
            new ChatOrchestrator(),
            $streamingChatProvider ?? new MockChatProvider(),
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

        $repository = $this->createMock(ArtifactRepositoryInterface::class);
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

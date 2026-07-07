<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat;

use App\Application\Chat\Commands\AskConversationChatStreamCommand;
use App\Application\Chat\ContentChatStreamer;
use App\Application\Chat\Handlers\AskConversationChatStreamHandler;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationContentMismatchException;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\SemanticRetriever;
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

final class AskConversationChatStreamHandlerTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';
    private const string MOCK_STREAMED_ANSWER = 'Mock answer based on retrieved context .';

    public function testCreatesConversationWhenMissingAndReturnsStreamEvents(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $contentId = new ContentId(self::CONTENT_ID);
        $saved = null;

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn(null);
        $conversationRepository
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (Conversation $conversation) use (&$saved): void {
                $saved = $conversation;
            });

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->expects(self::once())
            ->method('findByContentId')
            ->with($contentId)
            ->willReturn([]);

        $result = $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatStreamCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));

        self::assertNotNull($saved);
        self::assertTrue($saved->id()->equals($conversationId));
        self::assertCount(2, $saved->messages());
        self::assertSame(self::MOCK_STREAMED_ANSWER, $saved->messages()[1]->content());
        self::assertSame(self::CONVERSATION_ID, $result->conversation->id);
        self::assertSame(self::MOCK_STREAMED_ANSWER, $result->conversation->messages[1]->text);
        self::assertSame(
            ['Mock ', 'answer ', 'based ', 'on ', 'retrieved ', 'context ', '.'],
            array_map(static fn ($event): string => $event->text, $result->events),
        );
    }

    public function testAppendsToExistingConversation(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'Earlier question'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Earlier answer'));

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($existing);
        $conversationRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Conversation $conversation): bool {
                return 4 === count($conversation->messages())
                    && 'Follow-up question' === $conversation->messages()[2]->content()
                    && self::MOCK_STREAMED_ANSWER === $conversation->messages()[3]->content();
            }));

        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);

        $result = $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatStreamCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Follow-up question'));

        self::assertCount(4, $result->conversation->messages);
        self::assertSame('Earlier question', $result->conversation->messages[0]->text);
        self::assertSame('Follow-up question', $result->conversation->messages[2]->text);
    }

    public function testLoadsArtifactsInSelectedDocumentOrder(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID));

        $primaryArtifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            self::CONTENT_ID,
            'Primary artifact content',
        );
        $secondaryArtifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            self::OTHER_CONTENT_ID,
            'Secondary artifact content',
        );

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->method('findById')->willReturn($existing);
        $conversationRepository->expects(self::once())->method('save');

        $lookupOrder = [];
        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->method('findByContentId')
            ->willReturnCallback(static function (ContentId $contentId) use (
                &$lookupOrder,
                $primaryArtifact,
                $secondaryArtifact,
            ): array {
                $lookupOrder[] = $contentId->value;

                return match ($contentId->value) {
                    self::CONTENT_ID => [$primaryArtifact],
                    self::OTHER_CONTENT_ID => [$secondaryArtifact],
                    default => [],
                };
            });

        $result = $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatStreamCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Primary artifact content'));

        self::assertSame([self::CONTENT_ID, self::OTHER_CONTENT_ID], $lookupOrder);
        self::assertSame('Mock answer based on retrieved context [1][2].', $result->conversation->messages[1]->text);
    }

    public function testRejectsConversationWhenRouteContentIdIsNotSelected(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::OTHER_CONTENT_ID));

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($existing);
        $conversationRepository->expects(self::never())->method('save');

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->expects(self::never())->method('findByContentId');

        $this->expectException(ConversationContentMismatchException::class);

        $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatStreamCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));
    }

    private function createHandler(
        ConversationRepositoryInterface $conversationRepository,
        ArtifactRepositoryInterface $artifactRepository,
    ): AskConversationChatStreamHandler {
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new AskConversationChatStreamHandler(
            $conversationRepository,
            $artifactRepository,
            $this->createContentChatStreamer(),
            new RecordingPlatformLogger($contextProvider),
        );
    }

    private function createContentChatStreamer(): ContentChatStreamer
    {
        $vectorStore = new InMemoryVectorStore();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new ContentChatStreamer(
            new Chunker(),
            new DeterministicEmbeddingGenerator(new DeterministicEmbeddingProvider()),
            $vectorStore,
            new SemanticRetriever($vectorStore),
            new ChatOrchestrator(),
            new MockChatProvider(),
            new RecordingPlatformLogger($contextProvider),
            new RecordingPerformanceMetricsRecorder(),
            new FixedClock(),
        );
    }

    private function createArtifact(string $id, string $contentId, string $content): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            new ContentId($contentId),
            ProcessingJobId::generate(),
            ArtifactType::Summary,
            ArtifactContent::fromString($content),
        );
    }
}

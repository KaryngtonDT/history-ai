<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat;

use App\Application\Chat\Commands\AskConversationChatCommand;
use App\Application\Chat\ContentChatAnswerer;
use App\Application\Chat\Handlers\AskConversationChatHandler;
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

final class AskConversationChatHandlerTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string OTHER_CONTENT_ID = '550e8400-e29b-41d4-a716-446655440099';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testCreatesConversationWhenMissing(): void
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
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));

        self::assertNotNull($saved);
        self::assertTrue($saved->id()->equals($conversationId));
        self::assertTrue($saved->contentId()->equals($contentId));
        self::assertCount(2, $saved->messages());
        self::assertSame('Why did Rome fall?', $saved->messages()[0]->content());
        self::assertSame(MockChatProvider::MOCK_ANSWER, $saved->messages()[1]->content());
        self::assertSame(self::CONVERSATION_ID, $result->conversation->id);
        self::assertSame(self::CONTENT_ID, $result->conversation->contentId);
        self::assertSame(MockChatProvider::MOCK_ANSWER, $result->answer->answer);
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
                    && 'Earlier question' === $conversation->messages()[0]->content()
                    && 'Earlier answer' === $conversation->messages()[1]->content()
                    && 'Follow-up question' === $conversation->messages()[2]->content()
                    && MockChatProvider::MOCK_ANSWER === $conversation->messages()[3]->content();
            }));

        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);

        $result = $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Follow-up question'));

        self::assertCount(4, $result->conversation->messages);
        self::assertSame('user', $result->conversation->messages[0]->role);
        self::assertSame('assistant', $result->conversation->messages[1]->role);
        self::assertSame('user', $result->conversation->messages[2]->role);
        self::assertSame('assistant', $result->conversation->messages[3]->role);
    }

    public function testPreservesMessageOrder(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'One'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Two'));

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->method('findById')->willReturn($existing);
        $conversationRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Conversation $conversation): bool {
                return ['One', 'Two', 'Three', MockChatProvider::MOCK_ANSWER] === array_map(
                    static fn (ChatMessage $message): string => $message->content(),
                    $conversation->messages(),
                );
            }));

        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);

        $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Three'));
    }

    public function testRejectsConversationWhenRouteContentIdIsNotSelected(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::OTHER_CONTENT_ID));

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($existing);
        $conversationRepository->expects(self::never())->method('save');

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->expects(self::never())->method('findByContentId');

        $this->expectException(ConversationContentMismatchException::class);

        $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));
    }

    public function testAllowsRouteContentIdWhenItIsNotPrimaryDocument(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::OTHER_CONTENT_ID))
            ->addDocument(new ContentId(self::CONTENT_ID));

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->method('findById')->willReturn($existing);
        $conversationRepository->expects(self::once())->method('save');

        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);

        $result = $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));

        self::assertSame(MockChatProvider::MOCK_ANSWER, $result->answer->answer);
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
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Primary artifact content'));

        self::assertSame([self::CONTENT_ID, self::OTHER_CONTENT_ID], $lookupOrder);
        self::assertSame('Mock answer based on retrieved context [1][2].', $result->answer->answer);
        self::assertCount(2, $result->answer->sources);
        self::assertSame($primaryArtifact->id()->value, $result->answer->sources[0]->artifactId);
        self::assertSame($secondaryArtifact->id()->value, $result->answer->sources[1]->artifactId);
    }

    public function testReturnsMockAnswerWithEmptySourcesWhenNoArtifactsExistAcrossDocuments(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->addDocument(new ContentId(self::OTHER_CONTENT_ID));

        $conversationRepository = $this->createMock(ConversationRepositoryInterface::class);
        $conversationRepository->method('findById')->willReturn($existing);
        $conversationRepository->expects(self::once())->method('save');

        $artifactRepository = $this->createStub(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);

        $result = $this->createHandler($conversationRepository, $artifactRepository)
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));

        self::assertSame(MockChatProvider::MOCK_ANSWER, $result->answer->answer);
        self::assertSame([], $result->answer->sources);
        self::assertSame([], $result->answer->citations);
    }

    private function createHandler(
        ConversationRepositoryInterface $conversationRepository,
        ArtifactRepositoryInterface $artifactRepository,
    ): AskConversationChatHandler {
        return new AskConversationChatHandler(
            $conversationRepository,
            $artifactRepository,
            $this->createContentChatAnswerer(),
        );
    }

    private function createContentChatAnswerer(): ContentChatAnswerer
    {
        $vectorStore = new InMemoryVectorStore();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new ContentChatAnswerer(
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

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat;

use App\Application\Chat\Commands\AskConversationChatCommand;
use App\Application\Chat\Handlers\AskContentChatHandler;
use App\Application\Chat\Handlers\AskConversationChatHandler;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\ChatOrchestrator;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Chat\Exception\ConversationContentMismatchException;
use App\Domain\Content\ContentId;
use App\Domain\Semantic\Chunker;
use App\Domain\Semantic\SemanticRetriever;
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

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn(null);
        $repository
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (Conversation $conversation) use (&$saved): void {
                $saved = $conversation;
            });

        $result = (new AskConversationChatHandler($repository, $this->createContentChatHandler()))
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

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($existing);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Conversation $conversation): bool {
                return 4 === count($conversation->messages())
                    && 'Earlier question' === $conversation->messages()[0]->content()
                    && 'Earlier answer' === $conversation->messages()[1]->content()
                    && 'Follow-up question' === $conversation->messages()[2]->content()
                    && MockChatProvider::MOCK_ANSWER === $conversation->messages()[3]->content();
            }));

        $result = (new AskConversationChatHandler($repository, $this->createContentChatHandler()))
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

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository->method('findById')->willReturn($existing);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Conversation $conversation): bool {
                return ['One', 'Two', 'Three', MockChatProvider::MOCK_ANSWER] === array_map(
                    static fn (ChatMessage $message): string => $message->content(),
                    $conversation->messages(),
                );
            }));

        (new AskConversationChatHandler($repository, $this->createContentChatHandler()))
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Three'));
    }

    public function testRejectsConversationFromAnotherContent(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $existing = Conversation::start($conversationId, new ContentId(self::OTHER_CONTENT_ID));

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($existing);
        $repository->expects(self::never())->method('save');

        $this->expectException(ConversationContentMismatchException::class);

        (new AskConversationChatHandler($repository, $this->createContentChatHandler()))
            ->__invoke(new AskConversationChatCommand(self::CONTENT_ID, self::CONVERSATION_ID, 'Why did Rome fall?'));
    }

    private function createContentChatHandler(): AskContentChatHandler
    {
        $repository = $this->createMock(ArtifactRepositoryInterface::class);
        $repository->method('findByContentId')->willReturn([]);

        $vectorStore = new InMemoryVectorStore();
        $contextProvider = new FixedRequestContextProvider(new CorrelationId('c6f98b8a-3f2e-4a1b-9c8d-1e2f3a4b5c6d'));

        return new AskContentChatHandler(
            $repository,
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
}

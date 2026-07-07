<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;
use App\Domain\Content\ContentId;
use App\Infrastructure\Agent\ConversationMemoryToolExecutor;
use PHPUnit\Framework\TestCase;

final class ConversationMemoryToolExecutorTest extends TestCase
{
    private const string CONTENT_ID = '550e8400-e29b-41d4-a716-446655440000';
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testImplementsConversationMemoryToolExecutorInterface(): void
    {
        $executor = new ConversationMemoryToolExecutor(
            $this->createStub(ConversationRepositoryInterface::class),
        );

        self::assertInstanceOf(ConversationMemoryToolExecutorInterface::class, $executor);
    }

    public function testExecuteCallsConversationRepository(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $conversation = Conversation::start($conversationId, new ContentId(self::CONTENT_ID))
            ->appendUser(new ChatMessage(ChatMessageRole::User, 'What is Rome?'))
            ->appendAssistant(new ChatMessage(ChatMessageRole::Assistant, 'Rome was a city-state.'));

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn($conversation);

        $result = (new ConversationMemoryToolExecutor($repository))->execute(
            new ConversationMemoryExecution(self::CONVERSATION_ID, 'What did we discuss earlier?'),
        );

        self::assertSame('Conversation memory contains 2 messages.', $result->summary());
        self::assertSame(2, $result->messageCount());
        self::assertSame(
            [
                'messageCount' => 2,
                'userMessages' => 1,
                'assistantMessages' => 1,
            ],
            $result->metadata(),
        );
    }

    public function testExecuteReturnsEmptyResultForMissingConversation(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->with($conversationId)
            ->willReturn(null);

        $result = (new ConversationMemoryToolExecutor($repository))->execute(
            new ConversationMemoryExecution(self::CONVERSATION_ID, 'What did we discuss earlier?'),
        );

        self::assertSame('No conversation memory.', $result->summary());
        self::assertSame(0, $result->messageCount());
        self::assertSame([], $result->metadata());
    }

    public function testExecuteReturnsZeroCountsForEmptyConversation(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $conversation = Conversation::start($conversationId, new ContentId(self::CONTENT_ID));

        $repository = $this->createMock(ConversationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('findById')
            ->willReturn($conversation);

        $result = (new ConversationMemoryToolExecutor($repository))->execute(
            new ConversationMemoryExecution(self::CONVERSATION_ID, 'What did we discuss earlier?'),
        );

        self::assertSame('Conversation memory contains 0 messages.', $result->summary());
        self::assertSame(0, $result->messageCount());
        self::assertSame(
            [
                'messageCount' => 0,
                'userMessages' => 0,
                'assistantMessages' => 0,
            ],
            $result->metadata(),
        );
    }
}

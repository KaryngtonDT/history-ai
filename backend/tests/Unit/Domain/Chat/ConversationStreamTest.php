<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationStream;
use App\Domain\Chat\ConversationStreamEvent;
use App\Domain\Chat\ConversationStreamEventCollection;
use App\Domain\Chat\Exception\InvalidConversationStreamException;
use PHPUnit\Framework\TestCase;

final class ConversationStreamTest extends TestCase
{
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testExposesConversationIdAndEvents(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $events = new ConversationStreamEventCollection([
            new ConversationStreamEvent(0, new ChatToken('Hello')),
        ]);
        $stream = new ConversationStream($conversationId, $events);

        self::assertTrue($conversationId->equals($stream->conversationId()));
        self::assertSame($events, $stream->events());
    }

    public function testAppendReturnsNewStreamWithSequentialEvents(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $stream = new ConversationStream($conversationId, ConversationStreamEventCollection::empty());

        $updated = $stream
            ->append(new ChatToken('Several '))
            ->append(new ChatToken('factors contributed.'));

        self::assertSame(0, $stream->events()->count());
        self::assertSame(2, $updated->events()->count());
        self::assertSame(
            ['Several ', 'factors contributed.'],
            array_map(
                static fn (ConversationStreamEvent $event): string => $event->token()->text(),
                $updated->events()->events(),
            ),
        );
        self::assertTrue($conversationId->equals($updated->conversationId()));
    }

    public function testToAssistantMessageConcatenatesTokensInOrder(): void
    {
        $stream = new ConversationStream(
            new ConversationId(self::CONVERSATION_ID),
            new ConversationStreamEventCollection([
                new ConversationStreamEvent(0, new ChatToken('Several ')),
                new ConversationStreamEvent(1, new ChatToken('factors contributed.')),
            ]),
        );

        $message = $stream->toAssistantMessage();

        self::assertInstanceOf(ChatMessage::class, $message);
        self::assertSame(ChatMessageRole::Assistant, $message->role());
        self::assertSame('Several factors contributed.', $message->content());
    }

    public function testToAssistantMessageTrimsFinalContent(): void
    {
        $stream = new ConversationStream(
            new ConversationId(self::CONVERSATION_ID),
            new ConversationStreamEventCollection([
                new ConversationStreamEvent(0, new ChatToken('  Plain text answer  ')),
            ]),
        );

        self::assertSame('Plain text answer', $stream->toAssistantMessage()->content());
    }

    public function testToAssistantMessageRejectsEmptyStream(): void
    {
        $stream = new ConversationStream(
            new ConversationId(self::CONVERSATION_ID),
            ConversationStreamEventCollection::empty(),
        );

        $this->expectException(InvalidConversationStreamException::class);

        $stream->toAssistantMessage();
    }

    public function testIsImmutable(): void
    {
        $conversationId = new ConversationId(self::CONVERSATION_ID);
        $events = ConversationStreamEventCollection::empty();
        $stream = new ConversationStream($conversationId, $events);

        self::assertSame($events, $stream->events());
        self::assertSame($events, $stream->events());
    }
}

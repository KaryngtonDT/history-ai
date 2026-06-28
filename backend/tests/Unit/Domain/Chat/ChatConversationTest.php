<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatConversation;
use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;
use PHPUnit\Framework\TestCase;

final class ChatConversationTest extends TestCase
{
    public function testEmptyConversationIsAllowed(): void
    {
        $conversation = ChatConversation::empty();

        self::assertTrue($conversation->isEmpty());
        self::assertSame(0, $conversation->count());
        self::assertSame([], $conversation->messages());
    }

    public function testWithMessageAppendsImmutableHistory(): void
    {
        $conversation = ChatConversation::empty()
            ->withMessage(new ChatMessage(ChatMessageRole::User, 'Why did Rome fall?'))
            ->withMessage(new ChatMessage(ChatMessageRole::Assistant, 'Several factors contributed.'));

        self::assertSame(2, $conversation->count());
        self::assertFalse($conversation->isEmpty());
        self::assertSame(ChatMessageRole::User, $conversation->messages()[0]->role());
        self::assertSame(ChatMessageRole::Assistant, $conversation->messages()[1]->role());
        self::assertSame('Why did Rome fall?', $conversation->messages()[0]->content());
        self::assertSame('Several factors contributed.', $conversation->messages()[1]->content());
    }

    public function testOriginalConversationIsUnchangedAfterWithMessage(): void
    {
        $original = ChatConversation::empty();
        $extended = $original->withMessage(new ChatMessage(ChatMessageRole::User, 'Question?'));

        self::assertTrue($original->isEmpty());
        self::assertSame(1, $extended->count());
    }
}

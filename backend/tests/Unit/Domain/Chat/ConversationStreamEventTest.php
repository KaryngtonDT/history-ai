<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatToken;
use App\Domain\Chat\ConversationStreamEvent;
use App\Domain\Chat\Exception\InvalidConversationStreamException;
use PHPUnit\Framework\TestCase;

final class ConversationStreamEventTest extends TestCase
{
    public function testExposesIndexAndToken(): void
    {
        $token = new ChatToken('Hello');
        $event = new ConversationStreamEvent(0, $token);

        self::assertSame(0, $event->index());
        self::assertSame($token, $event->token());
    }

    public function testEqualsComparesIndexAndToken(): void
    {
        $token = new ChatToken('Hello');
        $otherToken = new ChatToken('World');

        self::assertTrue((new ConversationStreamEvent(0, $token))->equals(new ConversationStreamEvent(0, $token)));
        self::assertFalse((new ConversationStreamEvent(0, $token))->equals(new ConversationStreamEvent(1, $token)));
        self::assertFalse((new ConversationStreamEvent(0, $token))->equals(new ConversationStreamEvent(0, $otherToken)));
    }

    public function testRejectsNegativeIndex(): void
    {
        $this->expectException(InvalidConversationStreamException::class);

        new ConversationStreamEvent(-1, new ChatToken('Hello'));
    }

    public function testIsImmutable(): void
    {
        $token = new ChatToken('Stable fragment');
        $event = new ConversationStreamEvent(0, $token);

        self::assertSame(0, $event->index());
        self::assertSame($token, $event->token());
    }
}

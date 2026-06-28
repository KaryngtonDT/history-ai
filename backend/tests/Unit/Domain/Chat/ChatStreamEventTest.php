<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\Exception\InvalidChatStreamException;
use PHPUnit\Framework\TestCase;

final class ChatStreamEventTest extends TestCase
{
    public function testExposesIndexAndToken(): void
    {
        $token = new ChatToken('Hello');
        $event = new ChatStreamEvent(0, $token);

        self::assertSame(0, $event->index());
        self::assertSame($token, $event->token());
    }

    public function testEqualsComparesIndexAndToken(): void
    {
        $token = new ChatToken('Hello');
        $otherToken = new ChatToken('World');

        self::assertTrue((new ChatStreamEvent(0, $token))->equals(new ChatStreamEvent(0, $token)));
        self::assertFalse((new ChatStreamEvent(0, $token))->equals(new ChatStreamEvent(1, $token)));
        self::assertFalse((new ChatStreamEvent(0, $token))->equals(new ChatStreamEvent(0, $otherToken)));
    }

    public function testRejectsNegativeIndex(): void
    {
        $this->expectException(InvalidChatStreamException::class);

        new ChatStreamEvent(-1, new ChatToken('Hello'));
    }

    public function testIsImmutable(): void
    {
        $token = new ChatToken('Stable fragment');
        $event = new ChatStreamEvent(0, $token);

        self::assertSame(0, $event->index());
        self::assertSame($token, $event->token());
    }
}

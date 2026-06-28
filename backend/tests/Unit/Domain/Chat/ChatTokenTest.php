<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatToken;
use App\Domain\Chat\Exception\InvalidChatStreamException;
use PHPUnit\Framework\TestCase;

final class ChatTokenTest extends TestCase
{
    public function testExposesText(): void
    {
        $token = new ChatToken('Hello');

        self::assertSame('Hello', $token->text());
    }

    public function testPreservesWhitespaceInTokenText(): void
    {
        $token = new ChatToken(' world');

        self::assertSame(' world', $token->text());
    }

    public function testEqualsComparesText(): void
    {
        self::assertTrue((new ChatToken('Hello'))->equals(new ChatToken('Hello')));
        self::assertFalse((new ChatToken('Hello'))->equals(new ChatToken('World')));
    }

    public function testRejectsEmptyText(): void
    {
        $this->expectException(InvalidChatStreamException::class);

        new ChatToken('');
    }

    public function testRejectsWhitespaceOnlyText(): void
    {
        $this->expectException(InvalidChatStreamException::class);

        new ChatToken('   ');
    }

    public function testIsImmutable(): void
    {
        $token = new ChatToken('Stable fragment');

        self::assertSame('Stable fragment', $token->text());
        self::assertSame('Stable fragment', $token->text());
    }
}

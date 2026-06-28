<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatStreamEvent;
use App\Domain\Chat\ChatStreamEventCollection;
use App\Domain\Chat\ChatToken;
use App\Domain\Chat\Exception\InvalidChatStreamException;
use PHPUnit\Framework\TestCase;

final class ChatStreamEventCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = ChatStreamEventCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->events());
    }

    public function testPreservesEventOrderAndIndexing(): void
    {
        $collection = new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('Hello')),
            new ChatStreamEvent(1, new ChatToken(' world')),
        ]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            [0, 1],
            array_map(
                static fn (ChatStreamEvent $event): int => $event->index(),
                $collection->events(),
            ),
        );
        self::assertSame(
            ['Hello', ' world'],
            array_map(
                static fn (ChatStreamEvent $event): string => $event->token()->text(),
                $collection->events(),
            ),
        );
    }

    public function testRejectsNonSequentialIndexing(): void
    {
        $this->expectException(InvalidChatStreamException::class);

        new ChatStreamEventCollection([
            new ChatStreamEvent(1, new ChatToken('Hello')),
        ]);
    }

    public function testIsImmutable(): void
    {
        $collection = new ChatStreamEventCollection([
            new ChatStreamEvent(0, new ChatToken('Hello')),
        ]);

        self::assertSame(1, $collection->count());
        self::assertSame(1, $collection->count());
    }
}

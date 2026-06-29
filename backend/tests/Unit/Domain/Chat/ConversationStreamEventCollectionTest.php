<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\ChatToken;
use App\Domain\Chat\ConversationStreamEvent;
use App\Domain\Chat\ConversationStreamEventCollection;
use App\Domain\Chat\Exception\InvalidConversationStreamException;
use PHPUnit\Framework\TestCase;

final class ConversationStreamEventCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = ConversationStreamEventCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->events());
    }

    public function testPreservesEventOrderAndIndexing(): void
    {
        $collection = new ConversationStreamEventCollection([
            new ConversationStreamEvent(0, new ChatToken('Hello')),
            new ConversationStreamEvent(1, new ChatToken(' world')),
        ]);

        self::assertSame(2, $collection->count());
        self::assertFalse($collection->isEmpty());
        self::assertSame(
            [0, 1],
            array_map(
                static fn (ConversationStreamEvent $event): int => $event->index(),
                $collection->events(),
            ),
        );
        self::assertSame(
            ['Hello', ' world'],
            array_map(
                static fn (ConversationStreamEvent $event): string => $event->token()->text(),
                $collection->events(),
            ),
        );
    }

    public function testRejectsNonSequentialIndexing(): void
    {
        $this->expectException(InvalidConversationStreamException::class);

        new ConversationStreamEventCollection([
            new ConversationStreamEvent(1, new ChatToken('Hello')),
        ]);
    }

    public function testIsImmutable(): void
    {
        $collection = new ConversationStreamEventCollection([
            new ConversationStreamEvent(0, new ChatToken('Hello')),
        ]);

        self::assertSame(1, $collection->count());
        self::assertSame(1, $collection->count());
    }
}

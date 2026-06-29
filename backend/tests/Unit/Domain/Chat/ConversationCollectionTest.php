<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Chat;

use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationCollection;
use App\Domain\Chat\ConversationId;
use App\Domain\Content\ContentId;
use PHPUnit\Framework\TestCase;

final class ConversationCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = ConversationCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->conversations());
    }

    public function testPreservesInsertionOrder(): void
    {
        $first = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440001'),
            new ContentId('550e8400-e29b-41d4-a716-446655440000'),
        );
        $second = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440002'),
            new ContentId('550e8400-e29b-41d4-a716-446655440000'),
        );
        $third = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440003'),
            new ContentId('550e8400-e29b-41d4-a716-446655440000'),
        );

        $collection = new ConversationCollection([$first, $second, $third]);

        self::assertSame(3, $collection->count());
        self::assertSame(
            [
                '550e8400-e29b-41d4-a716-446655440001',
                '550e8400-e29b-41d4-a716-446655440002',
                '550e8400-e29b-41d4-a716-446655440003',
            ],
            array_map(
                static fn (Conversation $conversation): string => $conversation->id()->value,
                $collection->conversations(),
            ),
        );
    }

    public function testReturnedConversationsDoNotMutateCollection(): void
    {
        $collection = new ConversationCollection([
            Conversation::start(
                new ConversationId('550e8400-e29b-41d4-a716-446655440001'),
                new ContentId('550e8400-e29b-41d4-a716-446655440000'),
            ),
        ]);

        $conversations = $collection->conversations();
        $conversations[] = Conversation::start(
            new ConversationId('550e8400-e29b-41d4-a716-446655440002'),
            new ContentId('550e8400-e29b-41d4-a716-446655440000'),
        );

        self::assertSame(1, $collection->count());
    }
}

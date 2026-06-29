<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\ConversationMemoryResult;
use PHPUnit\Framework\TestCase;

final class ConversationMemoryResultTest extends TestCase
{
    public function testExposesSummaryMessageCountAndMetadata(): void
    {
        $result = new ConversationMemoryResult(
            'Conversation memory contains 4 messages.',
            4,
            ['userMessages' => 2, 'assistantMessages' => 2],
        );

        self::assertSame('Conversation memory contains 4 messages.', $result->summary());
        self::assertSame(4, $result->messageCount());
        self::assertSame(
            ['userMessages' => 2, 'assistantMessages' => 2],
            $result->metadata(),
        );
    }

    public function testEmptyFactoryReturnsNoConversationMemoryResult(): void
    {
        $result = ConversationMemoryResult::empty();

        self::assertSame('No conversation memory.', $result->summary());
        self::assertSame(0, $result->messageCount());
        self::assertSame([], $result->metadata());
    }

    public function testIsImmutable(): void
    {
        $result = new ConversationMemoryResult(
            'Conversation memory contains 2 messages.',
            2,
            ['userMessages' => 1, 'assistantMessages' => 1],
        );

        self::assertSame(2, $result->messageCount());
        self::assertSame(['userMessages' => 1, 'assistantMessages' => 1], $result->metadata());
    }

    public function testEqualsComparesSummaryMessageCountAndMetadata(): void
    {
        $first = new ConversationMemoryResult(
            'Conversation memory contains 3 messages.',
            3,
            ['userMessages' => 2, 'assistantMessages' => 1],
        );
        $second = new ConversationMemoryResult(
            'Conversation memory contains 3 messages.',
            3,
            ['userMessages' => 2, 'assistantMessages' => 1],
        );
        $differentMessageCount = new ConversationMemoryResult(
            'Conversation memory contains 3 messages.',
            5,
            ['userMessages' => 2, 'assistantMessages' => 1],
        );

        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($differentMessageCount));
    }
}

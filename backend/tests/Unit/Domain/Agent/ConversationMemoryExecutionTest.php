<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\ConversationMemoryExecution;
use PHPUnit\Framework\TestCase;

final class ConversationMemoryExecutionTest extends TestCase
{
    private const string CONVERSATION_ID = '550e8400-e29b-41d4-a716-446655440001';

    public function testExposesConversationIdAndQuestion(): void
    {
        $execution = new ConversationMemoryExecution(
            self::CONVERSATION_ID,
            'What did we discuss earlier?',
        );

        self::assertSame(self::CONVERSATION_ID, $execution->conversationId());
        self::assertSame('What did we discuss earlier?', $execution->question());
    }

    public function testIsImmutable(): void
    {
        $execution = new ConversationMemoryExecution(
            self::CONVERSATION_ID,
            'What did we discuss earlier?',
        );

        self::assertSame(self::CONVERSATION_ID, $execution->conversationId());
        self::assertSame('What did we discuss earlier?', $execution->question());
    }

    public function testEqualsComparesAllFields(): void
    {
        $first = new ConversationMemoryExecution(
            self::CONVERSATION_ID,
            'What did we discuss earlier?',
        );
        $second = new ConversationMemoryExecution(
            self::CONVERSATION_ID,
            'What did we discuss earlier?',
        );
        $differentQuestion = new ConversationMemoryExecution(
            self::CONVERSATION_ID,
            'Summarize our chat',
        );

        self::assertTrue($first->equals($second));
        self::assertFalse($first->equals($differentQuestion));
    }
}

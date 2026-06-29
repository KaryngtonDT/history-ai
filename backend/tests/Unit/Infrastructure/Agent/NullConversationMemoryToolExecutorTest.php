<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Agent;

use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;
use App\Infrastructure\Agent\NullConversationMemoryToolExecutor;
use PHPUnit\Framework\TestCase;

final class NullConversationMemoryToolExecutorTest extends TestCase
{
    private NullConversationMemoryToolExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = new NullConversationMemoryToolExecutor();
    }

    public function testImplementsConversationMemoryToolExecutorInterface(): void
    {
        self::assertInstanceOf(
            ConversationMemoryToolExecutorInterface::class,
            $this->executor,
        );
    }

    public function testExecuteReturnsNoConversationMemorySummary(): void
    {
        $execution = new ConversationMemoryExecution(
            '550e8400-e29b-41d4-a716-446655440001',
            'What did we discuss earlier?',
        );

        $result = $this->executor->execute($execution);

        self::assertSame('No conversation memory.', $result->summary());
    }

    public function testExecuteReturnsZeroMessageCount(): void
    {
        $execution = new ConversationMemoryExecution(
            '550e8400-e29b-41d4-a716-446655440001',
            'What did we discuss earlier?',
        );

        $result = $this->executor->execute($execution);

        self::assertSame(0, $result->messageCount());
    }

    public function testExecuteReturnsEmptyMetadata(): void
    {
        $execution = new ConversationMemoryExecution(
            '550e8400-e29b-41d4-a716-446655440001',
            'What did we discuss earlier?',
        );

        $result = $this->executor->execute($execution);

        self::assertSame([], $result->metadata());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use PHPUnit\Framework\TestCase;

final class AgentRequestTest extends TestCase
{
    public function testExposesTrimmedQuestion(): void
    {
        $request = new AgentRequest('  Compare Rome and Byzantium  ');

        self::assertSame('Compare Rome and Byzantium', $request->question());
    }

    public function testEqualsComparesQuestion(): void
    {
        $left = new AgentRequest('What is Rome?');
        $same = new AgentRequest('What is Rome?');
        $different = new AgentRequest('What is Byzantium?');

        self::assertTrue($left->equals($same));
        self::assertFalse($left->equals($different));
    }

    public function testRejectsEmptyQuestion(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent question cannot be empty');

        new AgentRequest('   ');
    }

    public function testRejectsTooLongQuestion(): void
    {
        $this->expectException(InvalidAgentPlanException::class);
        $this->expectExceptionMessage('Agent question cannot exceed 2000 characters');

        new AgentRequest(str_repeat('a', 2001));
    }

    public function testAcceptsQuestionAtMaxLength(): void
    {
        $question = str_repeat('a', 2000);

        $request = new AgentRequest($question);

        self::assertSame($question, $request->question());
    }

    public function testIsImmutable(): void
    {
        $request = new AgentRequest('What is Rome?');

        self::assertSame('What is Rome?', $request->question());
    }
}

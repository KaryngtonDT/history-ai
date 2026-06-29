<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Agent;

use App\Domain\Agent\AgentExecutionStatus;
use PHPUnit\Framework\TestCase;

final class AgentExecutionStatusTest extends TestCase
{
    public function testExposesAllSupportedStatuses(): void
    {
        self::assertSame(
            ['completed', 'skipped', 'failed'],
            array_map(
                static fn (AgentExecutionStatus $status): string => $status->value,
                AgentExecutionStatus::cases(),
            ),
        );
    }

    public function testEnumCasesAreStable(): void
    {
        self::assertSame(AgentExecutionStatus::Completed, AgentExecutionStatus::from('completed'));
        self::assertSame(AgentExecutionStatus::Skipped, AgentExecutionStatus::from('skipped'));
        self::assertSame(AgentExecutionStatus::Failed, AgentExecutionStatus::from('failed'));
    }
}

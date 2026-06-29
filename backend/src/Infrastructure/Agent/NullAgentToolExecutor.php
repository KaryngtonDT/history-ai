<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;

final class NullAgentToolExecutor implements AgentToolExecutorInterface
{
    public function execute(AgentToolExecution $execution): AgentToolExecutionResult
    {
        return new AgentToolExecutionResult(
            tool: $execution->tool(),
            summary: 'No execution.',
            metadata: [],
        );
    }
}

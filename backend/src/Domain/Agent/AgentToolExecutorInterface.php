<?php

declare(strict_types=1);

namespace App\Domain\Agent;

interface AgentToolExecutorInterface
{
    public function execute(AgentToolExecution $execution): AgentToolExecutionResult;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;

final class CompositeAgentToolExecutor implements AgentToolExecutorInterface
{
    public function __construct(
        private readonly AgentToolExecutorInterface $semanticSearchToolExecutor,
        private readonly AgentToolExecutorInterface $knowledgeGraphToolExecutor,
        private readonly AgentToolExecutorInterface $fallbackToolExecutor,
    ) {
    }

    public function execute(AgentToolExecution $execution): AgentToolExecutionResult
    {
        return match ($execution->tool()) {
            AgentTool::SemanticSearch => $this->semanticSearchToolExecutor->execute($execution),
            AgentTool::KnowledgeGraph => $this->knowledgeGraphToolExecutor->execute($execution),
            default => $this->fallbackToolExecutor->execute($execution),
        };
    }
}

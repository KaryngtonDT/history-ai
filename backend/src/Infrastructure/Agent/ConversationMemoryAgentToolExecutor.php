<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;
use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryResult;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;

final class ConversationMemoryAgentToolExecutor implements AgentToolExecutorInterface
{
    public function __construct(
        private readonly ConversationMemoryToolExecutorInterface $conversationMemoryToolExecutor,
    ) {
    }

    public function execute(AgentToolExecution $execution): AgentToolExecutionResult
    {
        $conversationId = $execution->conversationId();

        if (null === $conversationId || '' === trim($conversationId)) {
            $result = ConversationMemoryResult::empty();
        } else {
            $result = $this->conversationMemoryToolExecutor->execute(
                new ConversationMemoryExecution(
                    $conversationId,
                    $execution->question(),
                ),
            );
        }

        return new AgentToolExecutionResult(
            tool: AgentTool::ConversationMemory,
            summary: $result->summary(),
            metadata: $result->metadata(),
        );
    }
}

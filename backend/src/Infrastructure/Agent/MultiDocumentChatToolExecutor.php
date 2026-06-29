<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Application\Chat\Commands\AskConversationChatCommand;
use App\Application\Chat\Handlers\AskConversationChatHandler;
use App\Domain\Agent\AgentTool;
use App\Domain\Agent\AgentToolExecution;
use App\Domain\Agent\AgentToolExecutionResult;
use App\Domain\Agent\AgentToolExecutorInterface;

final class MultiDocumentChatToolExecutor implements AgentToolExecutorInterface
{
    public function __construct(
        private readonly AskConversationChatHandler $askConversationChatHandler,
    ) {
    }

    public function execute(AgentToolExecution $execution): AgentToolExecutionResult
    {
        $conversationId = $execution->conversationId();

        if (null === $conversationId || '' === trim($conversationId)) {
            return new AgentToolExecutionResult(
                tool: AgentTool::MultiDocumentChat,
                summary: 'Multi-document chat requires a conversation.',
                metadata: ['requiresConversation' => true],
            );
        }

        $chatResult = ($this->askConversationChatHandler)(
            new AskConversationChatCommand(
                $execution->contentId(),
                $conversationId,
                $execution->question(),
            ),
        );

        return new AgentToolExecutionResult(
            tool: AgentTool::MultiDocumentChat,
            summary: 'Multi-document chat generated an answer.',
            metadata: [
                'messageCount' => count($chatResult->conversation->messages),
                'sourceCount' => count($chatResult->answer->sources),
                'citationCount' => count($chatResult->answer->citations),
            ],
        );
    }
}

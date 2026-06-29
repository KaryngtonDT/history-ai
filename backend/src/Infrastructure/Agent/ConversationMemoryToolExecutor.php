<?php

declare(strict_types=1);

namespace App\Infrastructure\Agent;

use App\Domain\Agent\ConversationMemoryExecution;
use App\Domain\Agent\ConversationMemoryResult;
use App\Domain\Agent\ConversationMemoryToolExecutorInterface;
use App\Domain\Chat\ChatMessageRole;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\ConversationRepositoryInterface;

final class ConversationMemoryToolExecutor implements ConversationMemoryToolExecutorInterface
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {
    }

    public function execute(ConversationMemoryExecution $execution): ConversationMemoryResult
    {
        $conversation = $this->conversationRepository->findById(
            new ConversationId($execution->conversationId()),
        );

        if (null === $conversation) {
            return ConversationMemoryResult::empty();
        }

        $userMessages = 0;
        $assistantMessages = 0;

        foreach ($conversation->messages() as $message) {
            if (ChatMessageRole::User === $message->role()) {
                ++$userMessages;
            } else {
                ++$assistantMessages;
            }
        }

        $messageCount = $userMessages + $assistantMessages;

        return new ConversationMemoryResult(
            summary: sprintf('Conversation memory contains %d messages.', $messageCount),
            messageCount: $messageCount,
            metadata: [
                'messageCount' => $messageCount,
                'userMessages' => $userMessages,
                'assistantMessages' => $assistantMessages,
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Agent;

use App\Domain\Agent\AgentRequest;
use App\Domain\Agent\Exception\InvalidAgentPlanException;
use App\Domain\Chat\ConversationId;
use App\Domain\Chat\Exception\InvalidConversationIdException;
use App\Presentation\Http\Request\Agent\Exception\InvalidAgentRequestException;

final readonly class RunAgentRequest
{
    public function __construct(
        public string $question,
        public ?string $conversationId,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['question'])) {
            throw new InvalidAgentRequestException('Question is required.');
        }

        if (!is_string($payload['question'])) {
            throw new InvalidAgentRequestException('Question is required.');
        }

        try {
            new AgentRequest($payload['question']);
        } catch (InvalidAgentPlanException) {
            throw new InvalidAgentRequestException('Question is invalid.');
        }

        $conversationId = null;

        if (array_key_exists('conversationId', $payload) && null !== $payload['conversationId']) {
            if (!is_string($payload['conversationId'])) {
                throw new InvalidAgentRequestException('Conversation id is invalid.');
            }

            $trimmedConversationId = trim($payload['conversationId']);

            if ('' !== $trimmedConversationId) {
                try {
                    new ConversationId($trimmedConversationId);
                } catch (InvalidConversationIdException) {
                    throw new InvalidAgentRequestException('Conversation id is invalid.');
                }

                $conversationId = $trimmedConversationId;
            }
        }

        return new self($payload['question'], $conversationId);
    }
}

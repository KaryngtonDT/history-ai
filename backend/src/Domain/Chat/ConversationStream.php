<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidConversationStreamException;

final readonly class ConversationStream
{
    public function __construct(
        private ConversationId $conversationId,
        private ConversationStreamEventCollection $events,
    ) {
    }

    public function conversationId(): ConversationId
    {
        return $this->conversationId;
    }

    public function events(): ConversationStreamEventCollection
    {
        return $this->events;
    }

    public function append(ChatToken $token): self
    {
        $nextIndex = $this->events->count();

        return new self(
            $this->conversationId,
            new ConversationStreamEventCollection([
                ...$this->events->events(),
                new ConversationStreamEvent($nextIndex, $token),
            ]),
        );
    }

    public function toAssistantMessage(): ChatMessage
    {
        if ($this->events->isEmpty()) {
            throw new InvalidConversationStreamException(
                'Conversation stream must contain at least one event before creating an assistant message.',
            );
        }

        $content = '';

        foreach ($this->events->events() as $event) {
            $content .= $event->token()->text();
        }

        return new ChatMessage(ChatMessageRole::Assistant, $content);
    }
}

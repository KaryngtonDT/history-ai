<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidConversationMessageException;
use App\Domain\Content\ContentId;

final readonly class Conversation
{
    public function __construct(
        private ConversationId $id,
        private ContentId $contentId,
        private ChatConversation $conversation = new ChatConversation(),
    ) {
    }

    public static function start(ConversationId $id, ContentId $contentId): self
    {
        return new self($id, $contentId, ChatConversation::empty());
    }

    public function id(): ConversationId
    {
        return $this->id;
    }

    public function contentId(): ContentId
    {
        return $this->contentId;
    }

    /**
     * @return list<ChatMessage>
     */
    public function messages(): array
    {
        return $this->conversation->messages();
    }

    public function appendUser(ChatMessage $message): self
    {
        if (ChatMessageRole::User !== $message->role()) {
            throw new InvalidConversationMessageException(
                'appendUser() requires a user message.',
            );
        }

        return new self(
            $this->id,
            $this->contentId,
            $this->conversation->withMessage($message),
        );
    }

    public function appendAssistant(ChatMessage $message): self
    {
        if (ChatMessageRole::Assistant !== $message->role()) {
            throw new InvalidConversationMessageException(
                'appendAssistant() requires an assistant message.',
            );
        }

        return new self(
            $this->id,
            $this->contentId,
            $this->conversation->withMessage($message),
        );
    }
}

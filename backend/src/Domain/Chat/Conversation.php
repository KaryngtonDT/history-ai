<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidConversationDocumentException;
use App\Domain\Chat\Exception\InvalidConversationMessageException;
use App\Domain\Content\ContentId;

final readonly class Conversation
{
    private SelectedDocumentCollection $documents;

    public function __construct(
        private ConversationId $id,
        SelectedDocumentCollection|ContentId $documents,
        private ChatConversation $conversation = new ChatConversation(),
    ) {
        $this->documents = $documents instanceof ContentId
            ? SelectedDocumentCollection::fromContentId($documents)
            : $documents;

        if ($this->documents->count() < 1) {
            throw new InvalidConversationDocumentException(
                'A conversation must contain at least one document.',
            );
        }
    }

    public static function start(ConversationId $id, ContentId $contentId): self
    {
        return new self($id, SelectedDocumentCollection::fromContentId($contentId));
    }

    public function id(): ConversationId
    {
        return $this->id;
    }

    public function documents(): SelectedDocumentCollection
    {
        return $this->documents;
    }

    public function contentId(): ContentId
    {
        return $this->documents->all()[0]->contentId();
    }

    /**
     * @return list<ChatMessage>
     */
    public function messages(): array
    {
        return $this->conversation->messages();
    }

    public function containsDocument(ContentId $contentId): bool
    {
        return $this->documents->contains($contentId);
    }

    public function addDocument(ContentId $contentId): self
    {
        return new self(
            $this->id,
            $this->documents->add(new SelectedDocument($contentId)),
            $this->conversation,
        );
    }

    public function removeDocument(ContentId $contentId): self
    {
        return new self(
            $this->id,
            $this->documents->remove($contentId),
            $this->conversation,
        );
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
            $this->documents,
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
            $this->documents,
            $this->conversation->withMessage($message),
        );
    }
}

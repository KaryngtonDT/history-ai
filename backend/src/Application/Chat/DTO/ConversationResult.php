<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\Conversation;

final readonly class ConversationResult
{
    /**
     * @param list<ConversationMessageResult> $messages
     * @param list<SelectedDocumentResult>    $documents
     */
    public function __construct(
        public string $id,
        public string $contentId,
        public array $messages,
        public array $documents,
    ) {
    }

    public static function fromDomain(Conversation $conversation): self
    {
        return new self(
            id: $conversation->id()->value,
            contentId: $conversation->contentId()->value,
            messages: array_map(
                static fn (ChatMessage $message): ConversationMessageResult => ConversationMessageResult::fromDomain($message),
                $conversation->messages(),
            ),
            documents: array_map(
                static fn ($document): SelectedDocumentResult => SelectedDocumentResult::fromDomain($document),
                $conversation->documents()->all(),
            ),
        );
    }
}

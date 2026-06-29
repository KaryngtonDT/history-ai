<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Content\ContentId;

interface ConversationRepositoryInterface
{
    public function save(Conversation $conversation): void;

    public function findById(ConversationId $conversationId): ?Conversation;

    public function findByContentId(ContentId $contentId): ConversationCollection;
}

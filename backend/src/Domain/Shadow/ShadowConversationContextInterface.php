<?php

declare(strict_types=1);

namespace App\Domain\Shadow;

use App\Domain\Chat\ConversationId;

interface ShadowConversationContextInterface
{
    /**
     * @return list<string> recent conversation messages for prompt context
     */
    public function loadRecentMessages(ConversationId $conversationId, int $limit = 6): array;
}

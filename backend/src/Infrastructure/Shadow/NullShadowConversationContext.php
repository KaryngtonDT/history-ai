<?php

declare(strict_types=1);

namespace App\Infrastructure\Shadow;

use App\Domain\Chat\ConversationId;
use App\Domain\Shadow\ShadowConversationContextInterface;

final class NullShadowConversationContext implements ShadowConversationContextInterface
{
    public function loadRecentMessages(ConversationId $conversationId, int $limit = 6): array
    {
        return [];
    }
}

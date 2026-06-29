<?php

declare(strict_types=1);

namespace App\Application\Graph\Queries;

final readonly class GetConversationKnowledgeGraphQuery
{
    public function __construct(
        public string $conversationId,
    ) {
    }
}

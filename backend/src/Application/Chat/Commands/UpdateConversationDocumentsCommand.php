<?php

declare(strict_types=1);

namespace App\Application\Chat\Commands;

final readonly class UpdateConversationDocumentsCommand
{
    /**
     * @param list<string> $contentIds
     */
    public function __construct(
        public string $conversationId,
        public array $contentIds,
    ) {
    }
}

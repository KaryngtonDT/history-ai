<?php

declare(strict_types=1);

namespace App\Application\Agent\Commands;

final readonly class RunAgentCommand
{
    public function __construct(
        public string $contentId,
        public string $question,
        public ?string $conversationId = null,
    ) {
    }
}

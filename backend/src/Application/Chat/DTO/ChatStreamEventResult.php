<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatStreamEvent;

final readonly class ChatStreamEventResult
{
    public function __construct(
        public int $index,
        public string $text,
    ) {
    }

    public static function fromDomain(ChatStreamEvent $event): self
    {
        return new self(
            index: $event->index(),
            text: $event->token()->text(),
        );
    }
}

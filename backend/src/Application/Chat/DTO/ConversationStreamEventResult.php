<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ConversationStreamEvent;

final readonly class ConversationStreamEventResult
{
    public function __construct(
        public int $index,
        public string $text,
    ) {
    }

    public static function fromDomain(ConversationStreamEvent $event): self
    {
        return new self(
            index: $event->index(),
            text: $event->token()->text(),
        );
    }
}

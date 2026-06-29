<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\Conversation;
use App\Domain\Chat\ConversationStream;
use App\Domain\Chat\ConversationStreamEvent;

final readonly class ConversationChatStreamResult
{
    /**
     * @param list<ConversationStreamEventResult> $events
     */
    public function __construct(
        public array $events,
        public ConversationResult $conversation,
    ) {
    }

    public static function fromDomain(
        ConversationStream $stream,
        Conversation $conversation,
    ): self {
        return new self(
            events: array_map(
                static fn (ConversationStreamEvent $event): ConversationStreamEventResult => ConversationStreamEventResult::fromDomain($event),
                $stream->events()->events(),
            ),
            conversation: ConversationResult::fromDomain($conversation),
        );
    }
}

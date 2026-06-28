<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatStream;
use App\Domain\Chat\ChatStreamEvent;

final readonly class ChatStreamResult
{
    /**
     * @param list<ChatStreamEventResult> $events
     */
    public function __construct(
        public array $events,
    ) {
    }

    public static function fromDomain(ChatStream $stream): self
    {
        return new self(
            events: array_map(
                static fn (ChatStreamEvent $event): ChatStreamEventResult => ChatStreamEventResult::fromDomain($event),
                $stream->events()->events(),
            ),
        );
    }
}

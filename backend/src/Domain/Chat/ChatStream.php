<?php

declare(strict_types=1);

namespace App\Domain\Chat;

final readonly class ChatStream
{
    public function __construct(
        private ChatStreamEventCollection $events,
    ) {
    }

    public function events(): ChatStreamEventCollection
    {
        return $this->events;
    }

    public function toAnswer(): ChatAnswer
    {
        $answer = '';

        foreach ($this->events->events() as $event) {
            $answer .= $event->token()->text();
        }

        return new ChatAnswer($answer, ChatSourceCollection::empty());
    }
}

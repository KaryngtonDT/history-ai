<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatStreamException;

final readonly class ChatStreamEventCollection
{
    /** @var list<ChatStreamEvent> */
    private array $events;

    /**
     * @param list<ChatStreamEvent> $events
     */
    public function __construct(array $events)
    {
        $this->events = array_values($events);
        $this->assertSequentialIndexing();
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ChatStreamEvent>
     */
    public function events(): array
    {
        return $this->events;
    }

    public function count(): int
    {
        return count($this->events);
    }

    public function isEmpty(): bool
    {
        return [] === $this->events;
    }

    private function assertSequentialIndexing(): void
    {
        $expectedIndex = 0;

        foreach ($this->events as $event) {
            if ($event->index() !== $expectedIndex) {
                throw new InvalidChatStreamException(
                    sprintf(
                        'Chat stream events must be indexed sequentially from 0, expected %d, got %d.',
                        $expectedIndex,
                        $event->index(),
                    ),
                );
            }

            ++$expectedIndex;
        }
    }
}

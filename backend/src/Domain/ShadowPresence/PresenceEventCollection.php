<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresenceEventCollection
{
    /** @param list<PresenceEvent> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<PresenceEvent> */
    public function all(): array
    {
        return $this->items;
    }

    public function append(PresenceEvent $event): self
    {
        return new self([...$this->items, $event]);
    }

    public function last(): ?PresenceEvent
    {
        if ([] === $this->items) {
            return null;
        }

        return $this->items[array_key_last($this->items)];
    }
}

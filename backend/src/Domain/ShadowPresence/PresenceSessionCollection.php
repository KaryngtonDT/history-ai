<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

final readonly class PresenceSessionCollection
{
    /** @param list<PresenceSession> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<PresenceSession> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?PresenceSession
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(PresenceSession $session): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $session->id()) {
                $items[] = $item;
            }
        }

        $items[] = $session;

        return new self($items);
    }

    public function active(): ?PresenceSession
    {
        foreach ($this->items as $item) {
            if (PresenceState::Disconnected !== $item->state()) {
                return $item;
            }
        }

        return null;
    }
}

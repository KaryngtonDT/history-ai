<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

final readonly class MobileSessionCollection
{
    /** @param list<MobileSession> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<MobileSession> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?MobileSession
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(MobileSession $session): self
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
}

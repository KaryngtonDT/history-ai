<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

final readonly class BrowserSessionCollection
{
    /** @param list<BrowserSession> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<BrowserSession> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?BrowserSession
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(BrowserSession $session): self
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

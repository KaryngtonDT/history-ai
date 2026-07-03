<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

final readonly class BrowserActivityCollection
{
    /** @param list<BrowserActivity> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<BrowserActivity> */
    public function all(): array
    {
        return $this->items;
    }

    public function append(BrowserActivity $activity): self
    {
        return new self([...$this->items, $activity]);
    }

    public function last(): ?BrowserActivity
    {
        if ([] === $this->items) {
            return null;
        }

        return $this->items[array_key_last($this->items)];
    }
}

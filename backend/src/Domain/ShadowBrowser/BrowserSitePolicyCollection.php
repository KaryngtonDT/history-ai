<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

final readonly class BrowserSitePolicyCollection
{
    /** @param list<BrowserSitePolicy> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<BrowserSitePolicy> */
    public function all(): array
    {
        return $this->items;
    }

    public function findByHost(string $host): ?BrowserSitePolicy
    {
        $normalized = strtolower(trim($host));

        foreach ($this->items as $item) {
            if ($item->host() === $normalized) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(BrowserSitePolicy $policy): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->host() !== $policy->host()) {
                $items[] = $item;
            }
        }

        $items[] = $policy;

        return new self($items);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

final readonly class KnowledgeItemCollection
{
    /** @param list<KnowledgeItem> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeItem> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $key): ?KnowledgeItem
    {
        foreach ($this->items as $item) {
            if ($item->key() === $key) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(KnowledgeItem $item): self
    {
        $items = [];

        foreach ($this->items as $existing) {
            if ($existing->key() === $item->key()) {
                continue;
            }

            $items[] = $existing;
        }

        $items[] = $item;

        return new self($items);
    }

    /** @return list<KnowledgeItem> */
    public function byCategory(MemoryCategory $category): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (KnowledgeItem $item): bool => $item->category() === $category,
        ));
    }

    /** @return list<KnowledgeItem> */
    public function search(string $query): array
    {
        $needle = strtolower(trim($query));

        if ('' === $needle) {
            return $this->items;
        }

        return array_values(array_filter(
            $this->items,
            static fn (KnowledgeItem $item): bool => str_contains(strtolower($item->label()), $needle)
                || str_contains(strtolower($item->key()), $needle)
                || str_contains(strtolower($item->explanation()), $needle),
        ));
    }
}

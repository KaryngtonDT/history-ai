<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

final readonly class KnowledgeCollection
{
    /** @param list<KnowledgeEntry> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeEntry> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?KnowledgeEntry
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function findByKey(string $conceptKey): ?KnowledgeEntry
    {
        foreach ($this->items as $item) {
            if ($item->conceptKey() === $conceptKey) {
                return $item;
            }
        }

        return null;
    }

    public function append(KnowledgeEntry $entry): self
    {
        return new self([...$this->items, $entry]);
    }

    public function upsert(KnowledgeEntry $entry): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $entry->id() && $item->conceptKey() !== $entry->conceptKey()) {
                $items[] = $item;
            }
        }

        $items[] = $entry;

        return new self($items);
    }
}

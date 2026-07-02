<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

final readonly class KnowledgeMasteryCollection
{
    /** @param list<KnowledgeMastery> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeMastery> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $nodeKey): ?KnowledgeMastery
    {
        foreach ($this->items as $item) {
            if ($item->nodeKey() === $nodeKey) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(KnowledgeMastery $mastery): self
    {
        $items = [];

        foreach ($this->items as $existing) {
            if ($existing->nodeKey() === $mastery->nodeKey()) {
                continue;
            }

            $items[] = $existing;
        }

        $items[] = $mastery;

        return new self($items);
    }
}

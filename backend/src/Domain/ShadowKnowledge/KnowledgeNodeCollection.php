<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

final readonly class KnowledgeNodeCollection
{
    /** @param list<KnowledgeNode> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeNode> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $key): ?KnowledgeNode
    {
        foreach ($this->items as $item) {
            if ($item->key() === $key) {
                return $item;
            }
        }

        return null;
    }

    public function upsert(KnowledgeNode $node): self
    {
        $items = [];

        foreach ($this->items as $existing) {
            if ($existing->key() === $node->key()) {
                continue;
            }

            $items[] = $existing;
        }

        $items[] = $node;

        return new self($items);
    }

    /** @return list<KnowledgeNode> */
    public function search(string $query): array
    {
        $needle = strtolower(trim($query));

        if ('' === $needle) {
            return $this->items;
        }

        return array_values(array_filter(
            $this->items,
            static fn (KnowledgeNode $node): bool => str_contains(strtolower($node->label()), $needle)
                || str_contains(strtolower($node->key()), $needle),
        ));
    }
}

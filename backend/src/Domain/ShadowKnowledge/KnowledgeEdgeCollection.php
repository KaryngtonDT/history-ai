<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

final readonly class KnowledgeEdgeCollection
{
    /** @param list<KnowledgeEdge> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeEdge> */
    public function all(): array
    {
        return $this->items;
    }

    public function append(KnowledgeEdge $edge): self
    {
        foreach ($this->items as $existing) {
            if ($existing->fromKey() === $edge->fromKey()
                && $existing->toKey() === $edge->toKey()
                && $existing->type() === $edge->type()) {
                return $this;
            }
        }

        return new self([...$this->items, $edge]);
    }

    /** @return list<KnowledgeEdge> */
    public function forKey(string $key): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (KnowledgeEdge $edge): bool => $edge->fromKey() === $key || $edge->toKey() === $key,
        ));
    }

    /** @return list<KnowledgeEdge> */
    public function prerequisitesFor(string $key): array
    {
        return array_values(array_filter(
            $this->items,
            static fn (KnowledgeEdge $edge): bool => $edge->toKey() === $key
                && KnowledgeEdgeType::Prerequisite === $edge->type(),
        ));
    }
}

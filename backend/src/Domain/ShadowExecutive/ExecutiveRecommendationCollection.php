<?php

declare(strict_types=1);

namespace App\Domain\ShadowExecutive;

final readonly class ExecutiveRecommendationCollection
{
    /** @param list<ExecutiveRecommendation> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<ExecutiveRecommendation> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?ExecutiveRecommendation
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function append(ExecutiveRecommendation $recommendation): self
    {
        return new self([...$this->items, $recommendation]);
    }

    public function upsert(ExecutiveRecommendation $recommendation): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $recommendation->id()) {
                $items[] = $item;
            }
        }

        $items[] = $recommendation;

        return new self($items);
    }
}

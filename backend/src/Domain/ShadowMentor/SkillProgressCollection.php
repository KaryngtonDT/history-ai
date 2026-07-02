<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class SkillProgressCollection
{
    /** @param list<SkillProgress> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<SkillProgress> */
    public function all(): array
    {
        return $this->items;
    }

    public function upsert(SkillProgress $skill): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->key() !== $skill->key()) {
                $items[] = $item;
            }
        }

        $items[] = $skill;

        return new self($items);
    }
}

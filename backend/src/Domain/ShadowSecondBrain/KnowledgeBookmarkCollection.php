<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

final readonly class KnowledgeBookmarkCollection
{
    /** @param list<KnowledgeBookmark> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeBookmark> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?KnowledgeBookmark
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function append(KnowledgeBookmark $bookmark): self
    {
        return new self([...$this->items, $bookmark]);
    }

    public function upsert(KnowledgeBookmark $bookmark): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $bookmark->id()) {
                $items[] = $item;
            }
        }

        $items[] = $bookmark;

        return new self($items);
    }

    public function remove(string $id): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $id) {
                $items[] = $item;
            }
        }

        return new self($items);
    }
}

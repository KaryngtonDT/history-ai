<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

final readonly class KnowledgeNoteCollection
{
    /** @param list<KnowledgeNote> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeNote> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?KnowledgeNote
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function append(KnowledgeNote $note): self
    {
        return new self([...$this->items, $note]);
    }

    public function upsert(KnowledgeNote $note): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $note->id()) {
                $items[] = $item;
            }
        }

        $items[] = $note;

        return new self($items);
    }
}

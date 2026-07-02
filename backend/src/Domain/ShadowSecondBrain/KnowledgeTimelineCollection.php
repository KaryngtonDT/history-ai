<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

final readonly class KnowledgeTimelineCollection
{
    /** @param list<KnowledgeTimelineEvent> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<KnowledgeTimelineEvent> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?KnowledgeTimelineEvent
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function append(KnowledgeTimelineEvent $event): self
    {
        return new self([...$this->items, $event]);
    }

    public function upsert(KnowledgeTimelineEvent $event): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $event->id()) {
                $items[] = $item;
            }
        }

        $items[] = $event;

        return new self($items);
    }
}

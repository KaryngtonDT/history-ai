<?php

declare(strict_types=1);

namespace App\Domain\ShadowMentor;

final readonly class MentorMissionCollection
{
    /** @param list<MentorMission> $items */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<MentorMission> */
    public function all(): array
    {
        return $this->items;
    }

    public function find(string $id): ?MentorMission
    {
        foreach ($this->items as $item) {
            if ($item->id() === $id) {
                return $item;
            }
        }

        return null;
    }

    public function current(): ?MentorMission
    {
        foreach ($this->items as $item) {
            if (MentorMissionStatus::Active === $item->status()) {
                return $item;
            }
        }

        foreach ($this->items as $item) {
            if (MentorMissionStatus::Upcoming === $item->status()) {
                return $item;
            }
        }

        return null;
    }

    public function append(MentorMission $mission): self
    {
        return new self([...$this->items, $mission]);
    }

    public function upsert(MentorMission $mission): self
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item->id() !== $mission->id()) {
                $items[] = $item;
            }
        }

        $items[] = $mission;

        return new self($items);
    }
}

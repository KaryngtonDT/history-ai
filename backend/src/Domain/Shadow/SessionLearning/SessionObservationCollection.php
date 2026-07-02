<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

final readonly class SessionObservationCollection
{
    /**
     * @param list<SessionObservation> $items
     */
    public function __construct(private array $items)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<SessionObservation>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function append(SessionObservation $observation): self
    {
        return new self([...$this->items, $observation]);
    }

    public function countByType(SessionObservationType $type): int
    {
        return count(array_filter(
            $this->items,
            static fn (SessionObservation $item): bool => $item->type() === $type,
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipPendingChangeCollection
{
    /** @param list<RelationshipPendingChange> $changes */
    public function __construct(private array $changes)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<RelationshipPendingChange> */
    public function all(): array
    {
        return $this->changes;
    }

    public function append(RelationshipPendingChange $change): self
    {
        return new self([...$this->changes, $change]);
    }

    public function find(string $id): ?RelationshipPendingChange
    {
        foreach ($this->changes as $change) {
            if ($change->id() === $id) {
                return $change;
            }
        }

        return null;
    }

    public function replace(RelationshipPendingChange $updated): self
    {
        $changes = [];

        foreach ($this->changes as $change) {
            $changes[] = $change->id() === $updated->id() ? $updated : $change;
        }

        return new self($changes);
    }

    /** @return list<RelationshipPendingChange> */
    public function pending(): array
    {
        return array_values(array_filter(
            $this->changes,
            static fn (RelationshipPendingChange $change): bool => 'pending' === $change->status(),
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipTraitCollection
{
    /** @param list<RelationshipTrait> $traits */
    public function __construct(private array $traits)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<RelationshipTrait> */
    public function all(): array
    {
        return $this->traits;
    }

    public function find(string $type, string $key): ?RelationshipTrait
    {
        foreach ($this->traits as $trait) {
            if ($trait->type()->value === $type && $trait->key() === $key) {
                return $trait;
            }
        }

        return null;
    }

    public function upsert(RelationshipTrait $trait): self
    {
        $traits = [];

        foreach ($this->traits as $existing) {
            if ($existing->type() === $trait->type() && $existing->key() === $trait->key()) {
                continue;
            }

            $traits[] = $existing;
        }

        $traits[] = $trait;

        return new self($traits);
    }

    public function remove(string $type, string $key): self
    {
        return new self(array_values(array_filter(
            $this->traits,
            static fn (RelationshipTrait $trait): bool => !($trait->type()->value === $type && $trait->key() === $key),
        )));
    }

    /** @return list<RelationshipTrait> */
    public function enabled(): array
    {
        return array_values(array_filter($this->traits, static fn (RelationshipTrait $trait): bool => $trait->enabled()));
    }

    /** @return list<RelationshipTrait> */
    public function byType(RelationshipTraitType $type): array
    {
        return array_values(array_filter(
            $this->traits,
            static fn (RelationshipTrait $trait): bool => $trait->type() === $type,
        ));
    }
}

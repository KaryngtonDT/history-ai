<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipObservationCollection
{
    /** @param list<RelationshipObservation> $observations */
    public function __construct(private array $observations)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<RelationshipObservation> */
    public function all(): array
    {
        return $this->observations;
    }

    public function append(RelationshipObservation $observation): self
    {
        return new self([...$this->observations, $observation]);
    }
}

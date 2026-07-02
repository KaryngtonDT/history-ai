<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

final readonly class RelationshipSignalCollection
{
    /** @param list<RelationshipSignal> $signals */
    public function __construct(private array $signals)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /** @return list<RelationshipSignal> */
    public function all(): array
    {
        return $this->signals;
    }

    public function append(RelationshipSignal $signal): self
    {
        return new self([...$this->signals, $signal]);
    }
}

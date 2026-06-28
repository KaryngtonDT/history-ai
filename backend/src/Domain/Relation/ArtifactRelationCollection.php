<?php

declare(strict_types=1);

namespace App\Domain\Relation;

final readonly class ArtifactRelationCollection
{
    /** @var list<ArtifactRelation> */
    private array $relations;

    /**
     * @param list<ArtifactRelation> $relations
     */
    public function __construct(array $relations)
    {
        $this->relations = array_values($relations);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ArtifactRelation>
     */
    public function relations(): array
    {
        return $this->relations;
    }

    public function count(): int
    {
        return count($this->relations);
    }

    public function isEmpty(): bool
    {
        return [] === $this->relations;
    }
}

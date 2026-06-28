<?php

declare(strict_types=1);

namespace App\Application\Relation\DTO;

use App\Domain\Relation\ArtifactRelation;
use App\Domain\Relation\ArtifactRelationCollection;

final readonly class ArtifactRelationsResult
{
    /**
     * @param list<ArtifactRelationResult> $relations
     */
    public function __construct(
        public array $relations,
    ) {
    }

    public static function fromDomain(ArtifactRelationCollection $collection): self
    {
        return new self(
            relations: array_map(
                static fn (ArtifactRelation $relation): ArtifactRelationResult => ArtifactRelationResult::fromDomain(
                    $relation,
                ),
                $collection->relations(),
            ),
        );
    }
}

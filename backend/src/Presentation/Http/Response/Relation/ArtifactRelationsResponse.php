<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Relation;

use App\Application\Relation\DTO\ArtifactRelationResult;
use App\Application\Relation\DTO\ArtifactRelationsResult;

final class ArtifactRelationsResponse
{
    /**
     * @return array{
     *     relations: list<array{
     *         sourceArtifactId: string,
     *         targetArtifactId: string,
     *         type: string
     *     }>
     * }
     */
    public static function fromResult(ArtifactRelationsResult $result): array
    {
        return [
            'relations' => array_map(
                static fn (ArtifactRelationResult $relation): array => [
                    'sourceArtifactId' => $relation->sourceArtifactId,
                    'targetArtifactId' => $relation->targetArtifactId,
                    'type' => $relation->type,
                ],
                $result->relations,
            ),
        ];
    }
}

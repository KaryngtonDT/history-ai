<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Graph;

use App\Application\Graph\DTO\GraphEdgeResult;
use App\Application\Graph\DTO\GraphNeighborhoodResult;
use App\Application\Graph\DTO\GraphNodeResult;

final class GraphNeighborhoodResponse
{
    /**
     * @return array{
     *     center: array{artifactId: string, type: string, label: string},
     *     neighbors: list<array{artifactId: string, type: string, label: string}>,
     *     edges: list<array{
     *         sourceArtifactId: string,
     *         targetArtifactId: string,
     *         type: string,
     *         weight: float
     *     }>
     * }
     */
    public static function fromResult(GraphNeighborhoodResult $result): array
    {
        return [
            'center' => self::mapNode($result->center),
            'neighbors' => array_map(
                static fn (GraphNodeResult $node): array => self::mapNode($node),
                $result->neighbors,
            ),
            'edges' => array_map(
                static fn (GraphEdgeResult $edge): array => [
                    'sourceArtifactId' => $edge->sourceArtifactId,
                    'targetArtifactId' => $edge->targetArtifactId,
                    'type' => $edge->type,
                    'weight' => $edge->weight,
                ],
                $result->edges,
            ),
        ];
    }

    /**
     * @return array{artifactId: string, type: string, label: string}
     */
    private static function mapNode(GraphNodeResult $node): array
    {
        return [
            'artifactId' => $node->artifactId,
            'type' => $node->type,
            'label' => $node->title,
        ];
    }
}

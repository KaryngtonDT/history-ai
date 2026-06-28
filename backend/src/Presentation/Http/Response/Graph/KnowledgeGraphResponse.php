<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Graph;

use App\Application\Graph\DTO\GraphEdgeResult;
use App\Application\Graph\DTO\GraphNodeResult;
use App\Application\Graph\DTO\KnowledgeGraphResult;

final class KnowledgeGraphResponse
{
    /**
     * @return array{
     *     nodes: list<array{artifactId: string, type: string, title: string}>,
     *     edges: list<array{sourceArtifactId: string, targetArtifactId: string, type: string}>
     * }
     */
    public static function fromResult(KnowledgeGraphResult $result): array
    {
        return [
            'nodes' => array_map(
                static fn (GraphNodeResult $node): array => [
                    'artifactId' => $node->artifactId,
                    'type' => $node->type,
                    'title' => $node->title,
                ],
                $result->nodes,
            ),
            'edges' => array_map(
                static fn (GraphEdgeResult $edge): array => [
                    'sourceArtifactId' => $edge->sourceArtifactId,
                    'targetArtifactId' => $edge->targetArtifactId,
                    'type' => $edge->type,
                ],
                $result->edges,
            ),
        ];
    }
}

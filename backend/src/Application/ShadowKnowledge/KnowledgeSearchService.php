<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class KnowledgeSearchService
{
    public function search(KnowledgeGraph $graph, string $query): array
    {
        $nodes = $graph->nodes()->search($query);
        $edges = array_values(array_filter(
            $graph->edges()->all(),
            static fn ($edge): bool => str_contains(strtolower($edge->label()), strtolower($query))
                || str_contains(strtolower($edge->reason()), strtolower($query)),
        ));

        return [
            'query' => $query,
            'nodes' => array_map(
                static fn ($node) => [
                    'key' => $node->key(),
                    'label' => $node->label(),
                    'type' => $node->type()->value,
                ],
                $nodes,
            ),
            'edges' => array_map(
                static fn ($edge) => [
                    'id' => $edge->id(),
                    'label' => $edge->label(),
                    'fromKey' => $edge->fromKey(),
                    'toKey' => $edge->toKey(),
                ],
                $edges,
            ),
            'total' => count($nodes) + count($edges),
        ];
    }
}

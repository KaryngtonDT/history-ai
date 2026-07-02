<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowKnowledge\KnowledgeEdge;
use App\Domain\ShadowKnowledge\KnowledgeEdgeType;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeNode;

final class KnowledgeWorkspaceExplorer
{
    /**
     * @return list<array{key: string, label: string, children: list<mixed>}>
     */
    public function tree(KnowledgeGraph $graph): array
    {
        $nodes = $graph->nodes()->all();

        if ([] === $nodes) {
            return [];
        }

        /** @var array<string, KnowledgeNode> $nodeByKey */
        $nodeByKey = [];

        foreach ($nodes as $node) {
            $nodeByKey[$node->key()] = $node;
        }

        /** @var array<string, list<string>> $childrenByParent */
        $childrenByParent = [];

        foreach ($graph->edges()->all() as $edge) {
            if (!$this->isHierarchyEdge($edge)) {
                continue;
            }

            $parent = $edge->fromKey();
            $child = $edge->toKey();

            if (!isset($nodeByKey[$parent]) || !isset($nodeByKey[$child])) {
                continue;
            }

            $childrenByParent[$parent][] = $child;
        }

        $childKeys = [];

        foreach ($childrenByParent as $children) {
            foreach ($children as $child) {
                $childKeys[$child] = true;
            }
        }

        $roots = array_values(array_filter(
            array_keys($nodeByKey),
            static fn (string $key): bool => !isset($childKeys[$key]),
        ));

        if ([] === $roots) {
            $roots = array_map(static fn (KnowledgeNode $node): string => $node->key(), $nodes);
        }

        return array_map(
            fn (string $key): array => $this->nodeTree($key, $nodeByKey, $childrenByParent, []),
            $roots,
        );
    }

    /**
     * @param array<string, KnowledgeNode> $nodeByKey
     * @param array<string, list<string>> $childrenByParent
     * @param list<string> $visited
     *
     * @return array{key: string, label: string, children: list<mixed>}
     */
    private function nodeTree(
        string $key,
        array $nodeByKey,
        array $childrenByParent,
        array $visited,
    ): array {
        $node = $nodeByKey[$key];
        $visited[] = $key;
        $children = [];

        foreach ($childrenByParent[$key] ?? [] as $childKey) {
            if (in_array($childKey, $visited, true)) {
                continue;
            }

            $children[] = $this->nodeTree($childKey, $nodeByKey, $childrenByParent, $visited);
        }

        return [
            'key' => $node->key(),
            'label' => $node->label(),
            'children' => $children,
        ];
    }

    private function isHierarchyEdge(KnowledgeEdge $edge): bool
    {
        return in_array($edge->type(), [
            KnowledgeEdgeType::Prerequisite,
            KnowledgeEdgeType::Introduces,
            KnowledgeEdgeType::Extends,
            KnowledgeEdgeType::DependsOn,
        ], true);
    }
}

<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeEdge;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeMastery;
use App\Domain\ShadowKnowledge\KnowledgeNode;

final class KnowledgeJsonMapper
{
    public function __construct(
        private readonly KnowledgePathFinder $pathFinder,
        private readonly LearningGapDetector $gapDetector,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(KnowledgeGraph $graph): array
    {
        return [
            'id' => $graph->id()->value,
            'scopeKey' => $graph->scopeKey(),
            'graphEnabled' => $graph->graphEnabled(),
            'nodes' => array_map($this->nodeToArray(...), $graph->nodes()->all()),
            'edges' => array_map($this->edgeToArray(...), $graph->edges()->all()),
            'masteries' => array_map($this->masteryToArray(...), $graph->masteries()->all()),
            'paths' => $this->pathFinder->learningPaths($graph),
        ];
    }

    /** @return array<string, mixed> */
    public function nodeDetail(KnowledgeGraph $graph, string $key): array
    {
        $node = $graph->nodes()->find($key);

        if (null === $node) {
            return ['error' => 'Node not found.'];
        }

        return [
            'node' => $this->nodeToArray($node),
            'mastery' => $this->masteryToArray($graph->masteries()->find($key)),
            'related' => array_map($this->edgeToArray(...), $graph->edges()->forKey($key)),
            'gaps' => $this->gapDetector->detect($graph, $key),
        ];
    }

    /** @return array<string, mixed> */
    private function nodeToArray(KnowledgeNode $node): array
    {
        return [
            'key' => $node->key(),
            'label' => $node->label(),
            'type' => $node->type()->value,
            'explanation' => $node->explanation(),
            'sources' => $node->sources(),
        ];
    }

    /** @return array<string, mixed> */
    private function edgeToArray(KnowledgeEdge $edge): array
    {
        return [
            'id' => $edge->id(),
            'fromKey' => $edge->fromKey(),
            'toKey' => $edge->toKey(),
            'type' => $edge->type()->value,
            'label' => $edge->label(),
            'reason' => $edge->reason(),
            'source' => $edge->source(),
            'confidence' => $edge->confidence()->value,
        ];
    }

    /** @return array<string, mixed>|null */
    private function masteryToArray(?KnowledgeMastery $mastery): ?array
    {
        if (null === $mastery) {
            return null;
        }

        return [
            'nodeKey' => $mastery->nodeKey(),
            'percent' => $mastery->percent(),
            'exposureCount' => $mastery->exposureCount(),
            'exerciseCount' => $mastery->exerciseCount(),
            'explanationCount' => $mastery->explanationCount(),
            'videoIds' => $mastery->videoIds(),
            'confidence' => $mastery->confidence()->value,
            'mastered' => $mastery->mastered(),
        ];
    }
}

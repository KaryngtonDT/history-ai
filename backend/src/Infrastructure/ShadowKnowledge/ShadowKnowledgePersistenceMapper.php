<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowKnowledge;

use App\Domain\ShadowKnowledge\Exception\InvalidShadowKnowledgeException;
use App\Domain\ShadowKnowledge\KnowledgeConfidence;
use App\Domain\ShadowKnowledge\KnowledgeEdge;
use App\Domain\ShadowKnowledge\KnowledgeEdgeCollection;
use App\Domain\ShadowKnowledge\KnowledgeEdgeType;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeGraphId;
use App\Domain\ShadowKnowledge\KnowledgeMastery;
use App\Domain\ShadowKnowledge\KnowledgeMasteryCollection;
use App\Domain\ShadowKnowledge\KnowledgeNode;
use App\Domain\ShadowKnowledge\KnowledgeNodeCollection;
use App\Domain\ShadowKnowledge\KnowledgeNodeType;
use JsonException;

final class ShadowKnowledgePersistenceMapper
{
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
        ];
    }

    public function fromJson(string $json): KnowledgeGraph
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowKnowledgeException('Stored knowledge graph is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowKnowledgeException('Stored knowledge graph is invalid.');
        }

        return new KnowledgeGraph(
            new KnowledgeGraphId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->nodesFromArray(is_array($decoded['nodes'] ?? null) ? $decoded['nodes'] : []),
            $this->edgesFromArray(is_array($decoded['edges'] ?? null) ? $decoded['edges'] : []),
            $this->masteriesFromArray(is_array($decoded['masteries'] ?? null) ? $decoded['masteries'] : []),
            (bool) ($decoded['graphEnabled'] ?? true),
        );
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

    /** @return array<string, mixed> */
    private function masteryToArray(KnowledgeMastery $mastery): array
    {
        return [
            'nodeKey' => $mastery->nodeKey(),
            'percent' => $mastery->percent(),
            'exposureCount' => $mastery->exposureCount(),
            'exerciseCount' => $mastery->exerciseCount(),
            'explanationCount' => $mastery->explanationCount(),
            'videoIds' => $mastery->videoIds(),
            'confidence' => $mastery->confidence()->value,
        ];
    }

    /** @param list<array<string, mixed>> $items */
    private function nodesFromArray(array $items): KnowledgeNodeCollection
    {
        $nodes = [];

        foreach ($items as $item) {
            if (!is_string($item['key'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $nodes[] = KnowledgeNode::create(
                $item['key'],
                $item['label'],
                KnowledgeNodeType::tryFrom(is_string($item['type'] ?? null) ? $item['type'] : '') ?? KnowledgeNodeType::Concept,
                is_string($item['explanation'] ?? null) ? $item['explanation'] : '',
                is_array($item['sources'] ?? null) ? array_values(array_filter($item['sources'], 'is_string')) : [],
            );
        }

        return new KnowledgeNodeCollection($nodes);
    }

    /** @param list<array<string, mixed>> $items */
    private function edgesFromArray(array $items): KnowledgeEdgeCollection
    {
        $edges = KnowledgeEdgeCollection::empty();

        foreach ($items as $item) {
            if (!is_string($item['fromKey'] ?? null) || !is_string($item['toKey'] ?? null)) {
                continue;
            }

            $edges = $edges->append(new KnowledgeEdge(
                is_string($item['id'] ?? null) ? $item['id'] : bin2hex(random_bytes(8)),
                $item['fromKey'],
                $item['toKey'],
                KnowledgeEdgeType::tryFrom(is_string($item['type'] ?? null) ? $item['type'] : '') ?? KnowledgeEdgeType::RelatedTo,
                is_string($item['label'] ?? null) ? $item['label'] : '',
                is_string($item['reason'] ?? null) ? $item['reason'] : '',
                is_string($item['source'] ?? null) ? $item['source'] : 'stored',
                KnowledgeConfidence::tryFrom(is_string($item['confidence'] ?? null) ? $item['confidence'] : '') ?? KnowledgeConfidence::Medium,
            ));
        }

        return $edges;
    }

    /** @param list<array<string, mixed>> $items */
    private function masteriesFromArray(array $items): KnowledgeMasteryCollection
    {
        $masteries = [];

        foreach ($items as $item) {
            if (!is_string($item['nodeKey'] ?? null)) {
                continue;
            }

            $masteries[] = KnowledgeMastery::fromProgress(
                $item['nodeKey'],
                is_int($item['percent'] ?? null) ? $item['percent'] : 0,
                is_int($item['exposureCount'] ?? null) ? $item['exposureCount'] : 0,
                is_int($item['exerciseCount'] ?? null) ? $item['exerciseCount'] : 0,
                is_int($item['explanationCount'] ?? null) ? $item['explanationCount'] : 0,
                is_array($item['videoIds'] ?? null) ? array_values(array_filter($item['videoIds'], 'is_string')) : [],
            );
        }

        return new KnowledgeMasteryCollection($masteries);
    }
}

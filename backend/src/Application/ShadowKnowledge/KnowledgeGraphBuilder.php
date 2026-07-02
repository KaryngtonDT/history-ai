<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeMastery;
use App\Domain\ShadowKnowledge\KnowledgeNode;
use App\Domain\ShadowKnowledge\KnowledgeNodeType;
use App\Domain\ShadowTeaching\TeachingPlan;

final class KnowledgeGraphBuilder
{
    public function __construct(
        private readonly KnowledgeEdgeResolver $edgeResolver,
    ) {
    }

    public function build(
        KnowledgeGraph $graph,
        MemoryTimeline $memory,
        TeachingPlan $teaching,
    ): KnowledgeGraph {
        foreach ($memory->knowledge()->all() as $item) {
            $graph = $graph->upsertNode(KnowledgeNode::create(
                $item->key(),
                $item->label(),
                $this->edgeResolver->nodeTypeForKey($item->key()),
                $item->explanation(),
                ['memory'],
            ));

            $graph = $graph->upsertMastery(KnowledgeMastery::fromProgress(
                $item->key(),
                $item->progressPercent(),
                $item->exposureCount(),
                $item->questionCount(),
                $item->questionCount(),
                $item->videoIds(),
            ));
        }

        foreach ($teaching->objectives()->all() as $objective) {
            $graph = $graph->upsertNode(KnowledgeNode::create(
                $objective->key(),
                $objective->title(),
                KnowledgeNodeType::Concept,
                $objective->explanation(),
                ['teaching'],
            )->withSource('teaching'));

            $graph = $graph->upsertMastery(KnowledgeMastery::fromProgress(
                $objective->key(),
                $objective->progressPercent(),
                0,
                count($teaching->exercises()->forObjective($objective->key())->all()),
                1,
            ));
        }

        foreach ($teaching->missions()->all() as $mission) {
            $graph = $graph->upsertNode(KnowledgeNode::create(
                'mission_'.$mission->number(),
                $mission->title(),
                KnowledgeNodeType::Mission,
                $mission->rewardLabel(),
                ['teaching'],
            ));
        }

        return $graph->withEdges($this->edgeResolver->resolve($graph));
    }
}

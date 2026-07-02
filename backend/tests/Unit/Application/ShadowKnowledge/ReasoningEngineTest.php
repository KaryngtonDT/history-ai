<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\ShadowKnowledge;

use App\Application\ShadowKnowledge\GraphConceptResolver;
use App\Application\ShadowKnowledge\LearningGapDetector;
use App\Application\ShadowKnowledge\PrerequisiteChecker;
use App\Application\ShadowKnowledge\ReasoningEngine;
use App\Application\ShadowKnowledge\ReasoningExplanationBuilder;
use App\Domain\ShadowKnowledge\KnowledgeEdge;
use App\Domain\ShadowKnowledge\KnowledgeEdgeType;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeMastery;
use App\Domain\ShadowKnowledge\KnowledgeNode;
use App\Domain\ShadowKnowledge\KnowledgeNodeType;
use PHPUnit\Framework\TestCase;

final class ReasoningEngineTest extends TestCase
{
    private ReasoningEngine $engine;

    protected function setUp(): void
    {
        $prerequisiteChecker = new PrerequisiteChecker();
        $this->engine = new ReasoningEngine(
            new GraphConceptResolver(),
            $prerequisiteChecker,
            new LearningGapDetector($prerequisiteChecker),
            new ReasoningExplanationBuilder($prerequisiteChecker, new LearningGapDetector($prerequisiteChecker)),
        );
    }

    public function testReasonReturnsEmptyWhenGraphDisabled(): void
    {
        $graph = $this->graphWithDockerKubernetes(false);

        $result = $this->engine->reason($graph, 'Explain kubernetes');

        self::assertSame([], $result['promptLines']);
        self::assertSame(0, $result['readinessPercent']);
    }

    public function testReasonDetectsKubernetesConceptAndGaps(): void
    {
        $graph = $this->graphWithDockerKubernetes(true);

        $result = $this->engine->reason($graph, 'How does kubernetes orchestrate containers?');

        self::assertSame('kubernetes', $result['primaryKey']);
        self::assertSame('Kubernetes', $result['primaryLabel']);
        self::assertSame(0, $result['readinessPercent']);
        self::assertNotEmpty($result['gaps']);
        self::assertStringContainsString('Docker', implode("\n", $result['promptLines']));
    }

    public function testReasonReportsReadinessWhenPrerequisitesMastered(): void
    {
        $graph = $this->graphWithDockerKubernetes(true)
            ->upsertMastery(KnowledgeMastery::fromProgress('docker', 85));

        $result = $this->engine->reason($graph, 'kubernetes deployment basics');

        self::assertSame(100, $result['readinessPercent']);
        self::assertSame([], $result['gaps']);
        self::assertStringContainsString('100%', implode("\n", $result['promptLines']));
    }

    private function graphWithDockerKubernetes(bool $enabled): KnowledgeGraph
    {
        $graph = KnowledgeGraph::create();

        if (!$enabled) {
            return new KnowledgeGraph(
                $graph->id(),
                $graph->scopeKey(),
                $graph->nodes(),
                $graph->edges(),
                $graph->masteries(),
                false,
            );
        }

        return $graph
            ->upsertNode(KnowledgeNode::create('docker', 'Docker', KnowledgeNodeType::Technology))
            ->upsertNode(KnowledgeNode::create('kubernetes', 'Kubernetes', KnowledgeNodeType::Technology))
            ->addEdge(KnowledgeEdge::link(
                'docker',
                'kubernetes',
                KnowledgeEdgeType::Prerequisite,
                'Docker → Kubernetes',
                'Kubernetes orchestrates Docker containers.',
            ));
    }
}

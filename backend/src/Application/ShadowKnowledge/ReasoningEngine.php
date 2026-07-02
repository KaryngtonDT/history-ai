<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class ReasoningEngine
{
    public function __construct(
        private readonly GraphConceptResolver $conceptResolver,
        private readonly PrerequisiteChecker $prerequisiteChecker,
        private readonly LearningGapDetector $gapDetector,
        private readonly ReasoningExplanationBuilder $explanationBuilder,
    ) {
    }

    /** @return array{primaryKey: ?string, primaryLabel: ?string, readinessPercent: int, promptLines: list<string>, gaps: list<array<string, mixed>>} */
    public function reason(KnowledgeGraph $graph, string $question): array
    {
        if (!$graph->graphEnabled()) {
            return [
                'primaryKey' => null,
                'primaryLabel' => null,
                'readinessPercent' => 0,
                'promptLines' => [],
                'gaps' => [],
            ];
        }

        $concepts = $this->conceptResolver->extractConcepts($question);

        if ([] === $concepts) {
            return [
                'primaryKey' => null,
                'primaryLabel' => null,
                'readinessPercent' => 0,
                'promptLines' => [],
                'gaps' => [],
            ];
        }

        $primaryKey = $concepts[0]['key'];
        $primaryLabel = $concepts[0]['label'];

        return [
            'primaryKey' => $primaryKey,
            'primaryLabel' => $primaryLabel,
            'readinessPercent' => $this->prerequisiteChecker->readinessPercent($graph, $primaryKey),
            'promptLines' => $this->explanationBuilder->build($graph, $question, $primaryKey),
            'gaps' => $this->gapDetector->detect($graph, $primaryKey),
        ];
    }
}

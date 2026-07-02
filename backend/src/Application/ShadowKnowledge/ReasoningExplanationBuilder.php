<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class ReasoningExplanationBuilder
{
    public function __construct(
        private readonly PrerequisiteChecker $prerequisiteChecker,
        private readonly LearningGapDetector $gapDetector,
    ) {
    }

    /** @return list<string> */
    public function build(KnowledgeGraph $graph, string $question, string $primaryKey): array
    {
        $lines = [];
        $known = [];
        $missing = [];

        foreach ($this->prerequisiteChecker->prerequisitesFor($graph, $primaryKey) as $item) {
            if ($item['mastered']) {
                $known[] = $item['label'];
            } else {
                $missing[] = $item['label'];
            }
        }

        if ([] !== $known) {
            $lines[] = 'You already know: '.implode(', ', array_map(static fn (string $l) => '✓ '.$l, $known)).'.';
        }

        if ([] !== $missing) {
            $lines[] = 'Still missing: '.implode(', ', array_map(static fn (string $l) => '□ '.$l, $missing)).'.';
            $lines[] = 'Start with '.$missing[0].' before diving deeper into '
                .($graph->nodes()->find($primaryKey)?->label() ?? $primaryKey).'.';
        }

        $readiness = $this->prerequisiteChecker->readinessPercent($graph, $primaryKey);

        if ($readiness >= 80) {
            $lines[] = sprintf('You master about %d%% of prerequisites — ready to advance.', $readiness);
        } elseif ($readiness > 0) {
            $lines[] = sprintf('You cover %d%% of prerequisites; one more review may help.', $readiness);
        }

        $related = array_slice($graph->edges()->forKey($primaryKey), 0, 2);

        foreach ($related as $edge) {
            $lines[] = sprintf('Knowledge link: %s — %s', $edge->label(), $edge->reason());
        }

        if ([] !== $lines) {
            $lines[] = 'Use the knowledge graph naturally; explain why each prerequisite matters.';
        }

        return $lines;
    }
}

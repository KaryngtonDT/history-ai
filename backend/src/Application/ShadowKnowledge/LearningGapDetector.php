<?php

declare(strict_types=1);

namespace App\Application\ShadowKnowledge;

use App\Domain\ShadowKnowledge\KnowledgeGraph;

final class LearningGapDetector
{
    public function __construct(private readonly PrerequisiteChecker $prerequisiteChecker)
    {
    }

    /** @return list<array<string, mixed>> */
    public function detect(KnowledgeGraph $graph, string $targetKey): array
    {
        $gaps = [];

        foreach ($this->prerequisiteChecker->prerequisitesFor($graph, $targetKey) as $item) {
            if ($item['mastered']) {
                continue;
            }

            $mastery = $graph->masteries()->find($item['key']);

            $gaps[] = [
                'conceptKey' => $item['key'],
                'label' => $item['label'],
                'masteryPercent' => $mastery?->percent() ?? 0,
                'missing' => true,
                'recommended' => 'Review '.$item['label'].' before continuing.',
                'reason' => $item['reason'],
            ];
        }

        return $gaps;
    }

    /** @return list<array<string, mixed>> */
    public function radar(KnowledgeGraph $graph, string $goalKey): array
    {
        $readiness = $this->prerequisiteChecker->readinessPercent($graph, $goalKey);

        return [
            'goalKey' => $goalKey,
            'goalLabel' => $graph->nodes()->find($goalKey)?->label() ?? ucwords(str_replace('_', ' ', $goalKey)),
            'readinessPercent' => $readiness,
            'gaps' => $this->detect($graph, $goalKey),
        ];
    }
}

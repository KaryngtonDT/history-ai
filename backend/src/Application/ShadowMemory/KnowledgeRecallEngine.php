<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\KnowledgeRecall;
use App\Domain\ShadowMemory\MemoryTimeline;

final class KnowledgeRecallEngine
{
    public function __construct(
        private readonly KnowledgeSimilarityResolver $similarityResolver,
        private readonly KnowledgeConnectionBuilder $connectionBuilder,
    ) {
    }

    public function recall(MemoryTimeline $timeline, string $question): KnowledgeRecall
    {
        if (!$timeline->memoryEnabled()) {
            return KnowledgeRecall::empty();
        }

        $concepts = $this->similarityResolver->extractConcepts($question);

        if ([] === $concepts) {
            return KnowledgeRecall::empty();
        }

        $primaryKey = $concepts[0]['key'];
        $primary = $timeline->knowledge()->find($primaryKey);

        $prerequisite = $this->findPrerequisite($timeline, $primaryKey);
        $lines = [];

        if (null !== $prerequisite) {
            $lines[] = sprintf(
                'Before explaining %s, recall that the learner already studied %s (%d%% progress).',
                $concepts[0]['label'],
                $prerequisite->label(),
                $prerequisite->progressPercent(),
            );
        }

        if (null !== $primary) {
            $lines[] = sprintf(
                'Prior knowledge for %s: progress %d%%, seen in %d videos.',
                $primary->label(),
                $primary->progressPercent(),
                count($primary->videoIds()),
            );

            if ('mastered' === $primary->progress()->value) {
                $lines[] = sprintf('The learner already masters %s — go to the next step.', $primary->label());
            }
        } else {
            $lines[] = sprintf('This question touches %s, which is new or lightly covered so far.', $concepts[0]['label']);
        }

        $related = $this->connectionBuilder->build($timeline)->forKey($primaryKey);

        foreach (array_slice($related, 0, 2) as $connection) {
            $lines[] = sprintf('Knowledge connection: %s.', $connection->label());
        }

        if ([] !== $lines) {
            $lines[] = 'Use prior learning naturally; do not repeat full previous explanations unless needed.';
        }

        return new KnowledgeRecall(
            sprintf('Recalled knowledge for %s.', $concepts[0]['label']),
            $lines,
            $primary,
            $prerequisite,
        );
    }

    private function findPrerequisite(MemoryTimeline $timeline, string $key): ?KnowledgeItem
    {
        $map = [
            'symfony_messenger' => 'dependency_injection',
            'event_dispatcher' => 'dependency_injection',
            'kubernetes' => 'docker',
            'cuda' => 'gpu',
            'cqrs' => 'ddd',
        ];

        if (!isset($map[$key])) {
            return null;
        }

        return $timeline->knowledge()->find($map[$key]);
    }
}

<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\MemoryCategory;
use App\Domain\ShadowMemory\MemoryConfidence;
use App\Domain\ShadowMemory\MemoryEntry;
use App\Domain\ShadowMemory\MemoryImportance;
use App\Domain\ShadowMemory\MemoryTimeline;

final class MemoryEvolutionEngine
{
    public function __construct(
        private readonly KnowledgeSimilarityResolver $similarityResolver,
        private readonly KnowledgeConnectionBuilder $connectionBuilder,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function evolve(MemoryTimeline $timeline, string $kind, array $payload): MemoryTimeline
    {
        if (!$timeline->memoryEnabled()) {
            return $timeline;
        }

        $question = is_string($payload['question'] ?? null) ? $payload['question'] : '';
        $videoId = is_string($payload['videoId'] ?? null) ? $payload['videoId'] : null;
        $sessionId = is_string($payload['sessionId'] ?? null) ? $payload['sessionId'] : null;
        $concepts = $this->similarityResolver->extractConcepts($question.' '.json_encode($payload, JSON_THROW_ON_ERROR));

        foreach ($concepts as $concept) {
            $existing = $timeline->knowledge()->find($concept['key']);
            $item = null !== $existing
                ? $existing->withExposure($videoId, $sessionId)
                : KnowledgeItem::start(
                    $concept['key'],
                    $concept['label'],
                    MemoryCategory::Concept,
                    'Observed from learner activity.',
                )->withExposure($videoId, $sessionId);

            if ('question' === $kind) {
                $item = $item->withQuestion();
            }

            $timeline = $timeline
                ->upsertKnowledge($item)
                ->addEntry(MemoryEntry::record(
                    'question' === $kind ? MemoryCategory::Question : MemoryCategory::Concept,
                    $concept['label'],
                    sprintf('Recorded from %s activity.', $kind),
                    MemoryImportance::Normal,
                    MemoryConfidence::Medium,
                    $videoId,
                    is_int($payload['segmentIndex'] ?? null) ? $payload['segmentIndex'] : null,
                    $sessionId,
                    is_string($payload['conversationId'] ?? null) ? $payload['conversationId'] : null,
                    [$concept['key']],
                    [is_string($payload['source'] ?? null) ? $payload['source'] : 'shadow'],
                ));
        }

        if ('' !== $question && [] === $concepts) {
            $timeline = $timeline->addEntry(MemoryEntry::record(
                MemoryCategory::Question,
                mb_substr($question, 0, 120),
                'General watch-session question.',
                MemoryImportance::Normal,
                MemoryConfidence::Low,
                $videoId,
                null,
                $sessionId,
                null,
                [],
                ['shadow'],
            ));
        }

        return $timeline->withConnections($this->connectionBuilder->build($timeline));
    }
}

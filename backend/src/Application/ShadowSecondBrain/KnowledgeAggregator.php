<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Application\ShadowExecutive\ExecutiveCoordinator;
use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowMentor\MentorBuilder;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMemory\MemoryEntry;
use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use App\Domain\ShadowSecondBrain\KnowledgeSourceType;
use App\Domain\ShadowSecondBrain\KnowledgeTimelineCollection;
use App\Domain\ShadowSecondBrain\KnowledgeTimelineEvent;
use App\Domain\ShadowTeaching\TeachingMission;

final class KnowledgeAggregator
{
    public function __construct(
        private readonly KnowledgeBuilder $knowledgeBuilder,
        private readonly MemoryBuilder $memoryBuilder,
        private readonly MentorBuilder $mentorBuilder,
        private readonly ExecutiveCoordinator $executiveCoordinator,
        private readonly TeachingBuilder $teachingBuilder,
    ) {
    }

    /**
     * @return array{entries: KnowledgeCollection, timeline: KnowledgeTimelineCollection}
     */
    public function aggregate(string $scopeKey = 'default'): array
    {
        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);
        $memory = $this->memoryBuilder->ingestRelationship($scopeKey);
        $this->mentorBuilder->getPortfolio($scopeKey);
        $this->mentorBuilder->getPlan($scopeKey);
        $this->executiveCoordinator->getPlan($scopeKey);
        $teaching = $this->teachingBuilder->syncPlan($scopeKey);

        $entries = KnowledgeCollection::empty();
        $timeline = KnowledgeTimelineCollection::empty();

        foreach ($graph->nodes()->all() as $node) {
            $mastery = $graph->masteries()->find($node->key());
            $relatedKeys = $this->relatedKeysFor($graph, $node->key());
            $memoryItem = $memory->knowledge()->find($node->key());
            $now = new \DateTimeImmutable();

            $entries = $entries->upsert(new KnowledgeEntry(
                $node->key(),
                $node->key(),
                $node->label(),
                '' !== $node->explanation() ? $node->explanation() : $node->label(),
                $mastery?->percent() ?? $memoryItem?->progressPercent() ?? 0,
                $mastery?->firstSeenAt() ?? $memoryItem?->lastSeenAt() ?? $now,
                $mastery?->lastSeenAt() ?? $memoryItem?->lastSeenAt() ?? $now,
                $mastery?->exposureCount() ?? $memoryItem?->exposureCount() ?? 0,
                $mastery?->exerciseCount() ?? 0,
                $mastery?->explanationCount() ?? $memoryItem?->questionCount() ?? 0,
                $relatedKeys,
                $this->recommendationsFor($graph, $node->key()),
            ));
        }

        foreach ($memory->entries()->all() as $entry) {
            $timeline = $timeline->append($this->timelineFromMemory($entry));
        }

        foreach ($teaching->missions()->all() as $mission) {
            $timeline = $timeline->append($this->timelineFromMission($mission));
        }

        foreach ($teaching->history()->all() as $historyItem) {
            $timeline = $timeline->append(new KnowledgeTimelineEvent(
                'teaching_'.$historyItem->id(),
                $historyItem->label(),
                $historyItem->recordedAt(),
                null,
                KnowledgeSourceType::Teaching,
                $historyItem->id(),
            ));
        }

        return [
            'entries' => $entries,
            'timeline' => $timeline,
        ];
    }

    /** @return list<string> */
    private function relatedKeysFor(KnowledgeGraph $graph, string $key): array
    {
        $related = [];

        foreach ($graph->edges()->forKey($key) as $edge) {
            $related[] = $edge->fromKey() === $key ? $edge->toKey() : $edge->fromKey();
        }

        return array_values(array_unique($related));
    }

    /** @return list<string> */
    private function recommendationsFor(KnowledgeGraph $graph, string $key): array
    {
        $recommendations = [];

        foreach ($graph->edges()->forKey($key) as $edge) {
            if ($edge->fromKey() === $key) {
                $mastery = $graph->masteries()->find($edge->toKey());

                if (null !== $mastery && !$mastery->mastered()) {
                    $node = $graph->nodes()->find($edge->toKey());
                    $recommendations[] = $node?->label() ?? $edge->toKey();
                }
            }
        }

        return array_values(array_unique($recommendations));
    }

    private function timelineFromMemory(MemoryEntry $entry): KnowledgeTimelineEvent
    {
        $sourceType = null;
        $resourceId = $entry->videoId() ?? $entry->conversationId() ?? $entry->sessionId();

        if (null !== $entry->videoId()) {
            $sourceType = KnowledgeSourceType::Video;
        } elseif (null !== $entry->conversationId()) {
            $sourceType = KnowledgeSourceType::Conversation;
        }

        $conceptKey = $entry->concepts()[0] ?? null;

        return new KnowledgeTimelineEvent(
            'memory_'.$entry->id(),
            $entry->label(),
            $entry->recordedAt(),
            is_string($conceptKey) ? $conceptKey : null,
            $sourceType,
            is_string($resourceId) ? $resourceId : null,
        );
    }

    private function timelineFromMission(TeachingMission $mission): KnowledgeTimelineEvent
    {
        return new KnowledgeTimelineEvent(
            'mission_'.$mission->number(),
            $mission->title(),
            new \DateTimeImmutable(),
            $mission->objectiveKey(),
            KnowledgeSourceType::Mission,
            (string) $mission->number(),
        );
    }
}

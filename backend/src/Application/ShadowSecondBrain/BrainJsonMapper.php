<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Application\ShadowKnowledge\KnowledgeBuilder;
use App\Application\ShadowMemory\MemoryBuilder;
use App\Application\ShadowTeaching\TeachingBuilder;
use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowSecondBrain\KnowledgeBookmark;
use App\Domain\ShadowSecondBrain\KnowledgeDomainHeatmapEntry;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use App\Domain\ShadowSecondBrain\KnowledgeInsight;
use App\Domain\ShadowSecondBrain\KnowledgeNote;
use App\Domain\ShadowSecondBrain\KnowledgeSource;
use App\Domain\ShadowSecondBrain\KnowledgeSourceType;
use App\Domain\ShadowSecondBrain\KnowledgeTimelineEvent;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;
use App\Domain\ShadowTeaching\TeachingPlan;

final class BrainJsonMapper
{
    public function __construct(
        private readonly KnowledgeBuilder $knowledgeBuilder,
        private readonly MemoryBuilder $memoryBuilder,
        private readonly TeachingBuilder $teachingBuilder,
        private readonly KnowledgeWorkspaceExplorer $explorer,
        private readonly InsightGenerator $insightGenerator,
    ) {
    }

    /** @return array<string, mixed> */
    public function dashboard(KnowledgeWorkspace $workspace, string $scopeKey = 'default'): array
    {
        return [
            'scopeKey' => $scopeKey,
            'workspace' => $this->workspaceToArray($workspace),
            'insights' => array_map($this->insightToArray(...), $this->insightGenerator->generate($workspace)),
            'revisions' => $this->revisionsFor($workspace),
        ];
    }

    /** @return array<string, mixed> */
    public function concepts(KnowledgeWorkspace $workspace, string $scopeKey = 'default'): array
    {
        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);

        return [
            'scopeKey' => $workspace->scopeKey(),
            'tree' => array_map(
                fn (array $node): array => $this->treeNodeToArray($node, $workspace),
                $this->explorer->tree($graph),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function conceptDetail(KnowledgeWorkspace $workspace, string $id, string $scopeKey = 'default'): array
    {
        $entry = $workspace->entries()->find($id) ?? $workspace->findEntry($id);

        if (null === $entry) {
            return ['error' => 'Concept not found.'];
        }

        $graph = $this->knowledgeBuilder->syncGraph($scopeKey);
        $memory = $this->memoryBuilder->ingestRelationship($scopeKey);
        $teaching = $this->teachingBuilder->syncPlan($scopeKey);
        $sources = $this->sourcesFor($entry, $graph, $memory, $teaching);

        return [
            'scopeKey' => $scopeKey,
            'entry' => $this->entryToArray($entry),
            'sources' => array_map($this->sourceToArray(...), $sources),
            'evolution' => $this->evolutionFor($entry, $sources),
            'related' => $this->relatedEntries($workspace, $entry->relatedKeys()),
            'notes' => array_values(array_filter(
                array_map($this->noteToArray(...), $workspace->notes()->all()),
                static fn (array $note): bool => ($note['conceptKey'] ?? null) === $entry->conceptKey(),
            )),
        ];
    }

    /** @param list<KnowledgeEntry> $results */
    /** @return array<string, mixed> */
    public function searchResults(KnowledgeWorkspace $workspace, array $results, string $query): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'query' => $query,
            'hits' => array_map(
                fn (KnowledgeEntry $result): array => [
                    'conceptKey' => $result->conceptKey(),
                    'label' => $result->label(),
                    'summary' => $result->summary(),
                    'masteryPercent' => $result->masteryPercent(),
                    'sourceCount' => $result->exposureCount(),
                ],
                $results,
            ),
            'total' => count($results),
        ];
    }

    /** @return array<string, mixed> */
    public function timeline(KnowledgeWorkspace $workspace): array
    {
        $events = $workspace->timeline()->all();
        usort(
            $events,
            static fn (KnowledgeTimelineEvent $left, KnowledgeTimelineEvent $right): int => $right->occurredAt() <=> $left->occurredAt(),
        );

        return [
            'scopeKey' => $workspace->scopeKey(),
            'events' => array_map($this->timelineToArray(...), $events),
        ];
    }

    /** @param array<string, mixed> $diff */
    /** @return array<string, mixed> */
    public function diff(array $diff): array
    {
        return $diff;
    }

    /** @return array<string, mixed> */
    public function statistics(KnowledgeWorkspace $workspace): array
    {
        $statistics = $workspace->statistics();

        return [
            'scopeKey' => $workspace->scopeKey(),
            'videoCount' => $statistics->videoCount(),
            'pdfCount' => $statistics->pdfCount(),
            'conversationCount' => $statistics->conversationCount(),
            'exerciseCount' => $statistics->exerciseCount(),
            'missionCount' => $statistics->missionCount(),
            'conceptCount' => $statistics->conceptCount(),
            'domainHeatmap' => array_map(
                static fn (KnowledgeDomainHeatmapEntry $entry): array => [
                    'key' => $entry->key(),
                    'label' => $entry->label(),
                    'percent' => $entry->percent(),
                ],
                $statistics->domainHeatmap(),
            ),
        ];
    }

    /** @return array<string, mixed> */
    public function heatmap(KnowledgeWorkspace $workspace): array
    {
        return [
            'scopeKey' => $workspace->scopeKey(),
            'domains' => $this->statistics($workspace)['domainHeatmap'],
        ];
    }

    /** @return array<string, mixed> */
    private function entryToArray(KnowledgeEntry $entry): array
    {
        return [
            'id' => $entry->id(),
            'conceptKey' => $entry->conceptKey(),
            'label' => $entry->label(),
            'summary' => $entry->summary(),
            'masteryPercent' => $entry->masteryPercent(),
            'firstSeenAt' => $entry->firstSeenAt()->format(\DateTimeInterface::ATOM),
            'lastSeenAt' => $entry->lastSeenAt()->format(\DateTimeInterface::ATOM),
            'exposureCount' => $entry->exposureCount(),
            'exerciseCount' => $entry->exerciseCount(),
            'explanationCount' => $entry->explanationCount(),
            'relatedKeys' => $entry->relatedKeys(),
            'recommendations' => $entry->recommendations(),
        ];
    }

    /** @return array<string, mixed> */
    private function bookmarkToArray(KnowledgeBookmark $bookmark): array
    {
        return [
            'id' => $bookmark->id(),
            'label' => $bookmark->label(),
            'tags' => $bookmark->tags(),
            'conceptKey' => $bookmark->conceptKey(),
            'resourceType' => $bookmark->resourceType()?->value,
            'resourceId' => $bookmark->resourceId(),
        ];
    }

    /** @return array<string, mixed> */
    private function noteToArray(KnowledgeNote $note): array
    {
        return [
            'id' => $note->id(),
            'body' => $note->body(),
            'createdAt' => $note->createdAt()->format(\DateTimeInterface::ATOM),
            'conceptKey' => $note->conceptKey(),
        ];
    }

    /** @return array<string, mixed> */
    private function timelineToArray(KnowledgeTimelineEvent $event): array
    {
        return [
            'id' => $event->id(),
            'label' => $event->label(),
            'occurredAt' => $event->occurredAt()->format(\DateTimeInterface::ATOM),
            'conceptKey' => $event->conceptKey(),
            'sourceType' => $event->sourceType()?->value,
            'resourceId' => $event->resourceId(),
        ];
    }

    /** @return array<string, mixed> */
    private function insightToArray(KnowledgeInsight $insight): array
    {
        return [
            'id' => $insight->id(),
            'kind' => $insight->kind(),
            'label' => $insight->label(),
            'detail' => $insight->detail(),
            'conceptKey' => $insight->conceptKey(),
        ];
    }

    /** @return array<string, mixed> */
    private function sourceToArray(KnowledgeSource $source): array
    {
        return [
            'id' => $source->id(),
            'type' => $source->type()->value,
            'label' => $source->label(),
            'resourceId' => $source->resourceId(),
            'resourceLabel' => $source->resourceLabel(),
            'conceptKey' => $source->conceptKey(),
            'occurredAt' => $source->occurredAt()?->format(\DateTimeInterface::ATOM),
            'detail' => $source->detail(),
            'linkHint' => $source->linkHint(),
        ];
    }

    /** @return list<KnowledgeSource> */
    private function sourcesFor(
        KnowledgeEntry $entry,
        KnowledgeGraph $graph,
        MemoryTimeline $memory,
        TeachingPlan $teaching,
    ): array {
        $sources = [];
        $node = $graph->nodes()->find($entry->conceptKey());

        foreach ($node?->sources() ?? [] as $sourceLabel) {
            $sources[] = new KnowledgeSource(
                'graph_'.$entry->conceptKey().'_'.$sourceLabel,
                $this->sourceTypeFromLabel($sourceLabel),
                $sourceLabel,
                $entry->conceptKey(),
                $node?->label() ?? $entry->label(),
                $entry->conceptKey(),
            );
        }

        foreach ($memory->entries()->all() as $memoryEntry) {
            if (!in_array($entry->conceptKey(), $memoryEntry->concepts(), true)) {
                continue;
            }

            $sources[] = new KnowledgeSource(
                'memory_'.$memoryEntry->id(),
                null !== $memoryEntry->videoId() ? KnowledgeSourceType::Video : KnowledgeSourceType::Conversation,
                $memoryEntry->label(),
                $memoryEntry->videoId() ?? $memoryEntry->conversationId() ?? $memoryEntry->id(),
                $memoryEntry->label(),
                $entry->conceptKey(),
                $memoryEntry->recordedAt(),
                $memoryEntry->detail(),
                null !== $memoryEntry->segmentIndex() ? 'segment:'.$memoryEntry->segmentIndex() : null,
            );
        }

        foreach ($teaching->exercises()->all() as $exercise) {
            if ($exercise->objectiveKey() !== $entry->conceptKey()) {
                continue;
            }

            $sources[] = new KnowledgeSource(
                'exercise_'.$exercise->id(),
                KnowledgeSourceType::Exercise,
                $exercise->question(),
                $exercise->id(),
                $exercise->question(),
                $entry->conceptKey(),
            );
        }

        foreach ($teaching->missions()->all() as $mission) {
            if ($mission->objectiveKey() !== $entry->conceptKey()) {
                continue;
            }

            $sources[] = new KnowledgeSource(
                'mission_'.$mission->number(),
                KnowledgeSourceType::Mission,
                $mission->title(),
                (string) $mission->number(),
                $mission->title(),
                $entry->conceptKey(),
            );
        }

        return $sources;
    }

    /** @return array<string, mixed> */
    private function workspaceToArray(KnowledgeWorkspace $workspace): array
    {
        $events = $workspace->timeline()->all();
        usort(
            $events,
            static fn (KnowledgeTimelineEvent $left, KnowledgeTimelineEvent $right): int => $right->occurredAt() <=> $left->occurredAt(),
        );

        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'workspaceEnabled' => $workspace->workspaceEnabled(),
            'lastSyncedAt' => $workspace->lastSyncedAt()?->format(\DateTimeInterface::ATOM),
            'entries' => array_map($this->entryToArray(...), $workspace->entries()->all()),
            'bookmarks' => array_map($this->bookmarkToArray(...), $workspace->bookmarks()->all()),
            'notes' => array_map($this->noteToArray(...), $workspace->notes()->all()),
            'timeline' => array_map($this->timelineToArray(...), $events),
            'statistics' => $this->workspaceStatistics($workspace),
        ];
    }

    /** @return array<string, mixed> */
    private function workspaceStatistics(KnowledgeWorkspace $workspace): array
    {
        $statistics = $workspace->statistics();

        return [
            'videoCount' => $statistics->videoCount(),
            'pdfCount' => $statistics->pdfCount(),
            'conversationCount' => $statistics->conversationCount(),
            'exerciseCount' => $statistics->exerciseCount(),
            'missionCount' => $statistics->missionCount(),
            'conceptCount' => $statistics->conceptCount(),
            'domainHeatmap' => array_map(
                static fn (KnowledgeDomainHeatmapEntry $entry): array => [
                    'key' => $entry->key(),
                    'label' => $entry->label(),
                    'percent' => $entry->percent(),
                ],
                $statistics->domainHeatmap(),
            ),
        ];
    }

    /**
     * @param array{key: string, label: string, children: list<mixed>} $node
     *
     * @return array<string, mixed>
     */
    private function treeNodeToArray(array $node, KnowledgeWorkspace $workspace): array
    {
        $conceptKey = $node['key'];
        $entry = $workspace->findEntry($conceptKey);

        return [
            'id' => 'tree-'.$conceptKey,
            'label' => $node['label'],
            'conceptKey' => $conceptKey,
            'entryCount' => null !== $entry ? 1 : 0,
            'children' => array_map(
                fn (array $child): array => $this->treeNodeToArray($child, $workspace),
                $node['children'],
            ),
        ];
    }

    /** @param list<string> $relatedKeys */
    /** @return list<array<string, mixed>> */
    private function relatedEntries(KnowledgeWorkspace $workspace, array $relatedKeys): array
    {
        $related = [];

        foreach ($relatedKeys as $relatedKey) {
            $relatedEntry = $workspace->findEntry($relatedKey);

            if (null === $relatedEntry) {
                continue;
            }

            $related[] = $this->entryToArray($relatedEntry);
        }

        return $related;
    }

    /** @return list<array<string, mixed>> */
    private function revisionsFor(KnowledgeWorkspace $workspace): array
    {
        $revisions = [];

        foreach ($workspace->entries()->all() as $entry) {
            if ($entry->masteryPercent() >= 60) {
                continue;
            }

            $revisions[] = [
                'conceptKey' => $entry->conceptKey(),
                'dueAt' => $entry->lastSeenAt()->modify('+7 days')->format(\DateTimeInterface::ATOM),
                'reason' => 'Mastery below revision threshold',
            ];
        }

        return $revisions;
    }

    /** @param list<KnowledgeSource> $sources */
    /** @return array<string, mixed> */
    private function evolutionFor(KnowledgeEntry $entry, array $sources): array {
        $videoCount = count(array_filter(
            $sources,
            static fn (KnowledgeSource $source): bool => KnowledgeSourceType::Video === $source->type()
                || KnowledgeSourceType::Youtube === $source->type(),
        ));

        return [
            'conceptKey' => $entry->conceptKey(),
            'firstSeenAt' => $entry->firstSeenAt()->format(\DateTimeInterface::ATOM),
            'explanationCount' => $entry->explanationCount(),
            'videoCount' => $videoCount,
            'exerciseCount' => $entry->exerciseCount(),
            'lastRevisionAt' => $entry->lastSeenAt()->format(\DateTimeInterface::ATOM),
            'masteryPercent' => $entry->masteryPercent(),
        ];
    }

    private function sourceTypeFromLabel(string $label): KnowledgeSourceType
    {
        return match (strtolower($label)) {
            'memory' => KnowledgeSourceType::Conversation,
            'teaching' => KnowledgeSourceType::Teaching,
            'video' => KnowledgeSourceType::Video,
            'pdf' => KnowledgeSourceType::Pdf,
            default => KnowledgeSourceType::Conversation,
        };
    }
}

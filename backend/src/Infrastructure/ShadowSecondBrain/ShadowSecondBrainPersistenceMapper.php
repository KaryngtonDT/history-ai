<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;
use App\Domain\ShadowSecondBrain\KnowledgeBookmark;
use App\Domain\ShadowSecondBrain\KnowledgeBookmarkCollection;
use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeDomainHeatmapEntry;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;
use App\Domain\ShadowSecondBrain\KnowledgeNote;
use App\Domain\ShadowSecondBrain\KnowledgeNoteCollection;
use App\Domain\ShadowSecondBrain\KnowledgeSourceType;
use App\Domain\ShadowSecondBrain\KnowledgeStatistics;
use App\Domain\ShadowSecondBrain\KnowledgeTimelineCollection;
use App\Domain\ShadowSecondBrain\KnowledgeTimelineEvent;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspaceId;
use JsonException;

final class ShadowSecondBrainPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(KnowledgeWorkspace $workspace): array
    {
        return [
            'id' => $workspace->id()->value,
            'scopeKey' => $workspace->scopeKey(),
            'workspaceEnabled' => $workspace->workspaceEnabled(),
            'lastSyncedAt' => $workspace->lastSyncedAt()?->format(\DateTimeInterface::ATOM),
            'entries' => array_map($this->entryToArray(...), $workspace->entries()->all()),
            'bookmarks' => array_map($this->bookmarkToArray(...), $workspace->bookmarks()->all()),
            'notes' => array_map($this->noteToArray(...), $workspace->notes()->all()),
            'timeline' => array_map($this->timelineToArray(...), $workspace->timeline()->all()),
            'statistics' => $this->statisticsToArray($workspace->statistics()),
        ];
    }

    public function fromJson(string $json): KnowledgeWorkspace
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowSecondBrainException('Stored knowledge workspace is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowSecondBrainException('Stored knowledge workspace is invalid.');
        }

        return new KnowledgeWorkspace(
            new KnowledgeWorkspaceId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->entriesFromArray(is_array($decoded['entries'] ?? null) ? $decoded['entries'] : []),
            $this->bookmarksFromArray(is_array($decoded['bookmarks'] ?? null) ? $decoded['bookmarks'] : []),
            $this->notesFromArray(is_array($decoded['notes'] ?? null) ? $decoded['notes'] : []),
            $this->timelineFromArray(is_array($decoded['timeline'] ?? null) ? $decoded['timeline'] : []),
            $this->statisticsFromArray(is_array($decoded['statistics'] ?? null) ? $decoded['statistics'] : []),
            (bool) ($decoded['workspaceEnabled'] ?? true),
            is_string($decoded['lastSyncedAt'] ?? null)
                ? new \DateTimeImmutable($decoded['lastSyncedAt'])
                : null,
        );
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
    private function statisticsToArray(KnowledgeStatistics $statistics): array
    {
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

    /** @param list<array<string, mixed>> $items */
    private function entriesFromArray(array $items): KnowledgeCollection
    {
        $entries = [];

        foreach ($items as $item) {
            if (!is_string($item['conceptKey'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $entries[] = new KnowledgeEntry(
                is_string($item['id'] ?? null) ? $item['id'] : $item['conceptKey'],
                $item['conceptKey'],
                $item['label'],
                is_string($item['summary'] ?? null) ? $item['summary'] : '',
                is_int($item['masteryPercent'] ?? null) ? $item['masteryPercent'] : 0,
                new \DateTimeImmutable(is_string($item['firstSeenAt'] ?? null) ? $item['firstSeenAt'] : 'now'),
                new \DateTimeImmutable(is_string($item['lastSeenAt'] ?? null) ? $item['lastSeenAt'] : 'now'),
                is_int($item['exposureCount'] ?? null) ? $item['exposureCount'] : 0,
                is_int($item['exerciseCount'] ?? null) ? $item['exerciseCount'] : 0,
                is_int($item['explanationCount'] ?? null) ? $item['explanationCount'] : 0,
                is_array($item['relatedKeys'] ?? null) ? array_values(array_filter($item['relatedKeys'], 'is_string')) : [],
                is_array($item['recommendations'] ?? null) ? array_values(array_filter($item['recommendations'], 'is_string')) : [],
            );
        }

        return new KnowledgeCollection($entries);
    }

    /** @param list<array<string, mixed>> $items */
    private function bookmarksFromArray(array $items): KnowledgeBookmarkCollection
    {
        $bookmarks = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $bookmarks[] = new KnowledgeBookmark(
                $item['id'],
                $item['label'],
                is_array($item['tags'] ?? null) ? array_values(array_filter($item['tags'], 'is_string')) : [],
                is_string($item['conceptKey'] ?? null) ? $item['conceptKey'] : null,
                is_string($item['resourceType'] ?? null) ? KnowledgeSourceType::tryFrom($item['resourceType']) : null,
                is_string($item['resourceId'] ?? null) ? $item['resourceId'] : null,
            );
        }

        return new KnowledgeBookmarkCollection($bookmarks);
    }

    /** @param list<array<string, mixed>> $items */
    private function notesFromArray(array $items): KnowledgeNoteCollection
    {
        $notes = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['body'] ?? null)) {
                continue;
            }

            $notes[] = new KnowledgeNote(
                $item['id'],
                $item['body'],
                new \DateTimeImmutable(is_string($item['createdAt'] ?? null) ? $item['createdAt'] : 'now'),
                is_string($item['conceptKey'] ?? null) ? $item['conceptKey'] : null,
            );
        }

        return new KnowledgeNoteCollection($notes);
    }

    /** @param list<array<string, mixed>> $items */
    private function timelineFromArray(array $items): KnowledgeTimelineCollection
    {
        $events = [];

        foreach ($items as $item) {
            if (!is_string($item['id'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $events[] = new KnowledgeTimelineEvent(
                $item['id'],
                $item['label'],
                new \DateTimeImmutable(is_string($item['occurredAt'] ?? null) ? $item['occurredAt'] : 'now'),
                is_string($item['conceptKey'] ?? null) ? $item['conceptKey'] : null,
                is_string($item['sourceType'] ?? null) ? KnowledgeSourceType::tryFrom($item['sourceType']) : null,
                is_string($item['resourceId'] ?? null) ? $item['resourceId'] : null,
            );
        }

        return new KnowledgeTimelineCollection($events);
    }

    /** @param array<string, mixed> $data */
    private function statisticsFromArray(array $data): KnowledgeStatistics
    {
        $heatmap = [];

        foreach (is_array($data['domainHeatmap'] ?? null) ? $data['domainHeatmap'] : [] as $item) {
            if (!is_string($item['key'] ?? null) || !is_string($item['label'] ?? null)) {
                continue;
            }

            $heatmap[] = new KnowledgeDomainHeatmapEntry(
                $item['key'],
                $item['label'],
                is_int($item['percent'] ?? null) ? $item['percent'] : 0,
            );
        }

        return new KnowledgeStatistics(
            is_int($data['videoCount'] ?? null) ? $data['videoCount'] : 0,
            is_int($data['pdfCount'] ?? null) ? $data['pdfCount'] : 0,
            is_int($data['conversationCount'] ?? null) ? $data['conversationCount'] : 0,
            is_int($data['exerciseCount'] ?? null) ? $data['exerciseCount'] : 0,
            is_int($data['missionCount'] ?? null) ? $data['missionCount'] : 0,
            is_int($data['conceptCount'] ?? null) ? $data['conceptCount'] : 0,
            $heatmap,
        );
    }
}

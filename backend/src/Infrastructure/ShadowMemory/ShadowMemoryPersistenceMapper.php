<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowMemory;

use App\Domain\ShadowMemory\Exception\InvalidShadowMemoryException;
use App\Domain\ShadowMemory\KnowledgeConnection;
use App\Domain\ShadowMemory\KnowledgeConnectionCollection;
use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\KnowledgeItemCollection;
use App\Domain\ShadowMemory\KnowledgeProgress;
use App\Domain\ShadowMemory\MemoryCategory;
use App\Domain\ShadowMemory\MemoryConfidence;
use App\Domain\ShadowMemory\MemoryEntry;
use App\Domain\ShadowMemory\MemoryEntryCollection;
use App\Domain\ShadowMemory\MemoryImportance;
use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowMemory\MemoryTimelineId;
use JsonException;

final class ShadowMemoryPersistenceMapper
{
    /** @return array<string, mixed> */
    public function toArray(MemoryTimeline $timeline): array
    {
        return [
            'id' => $timeline->id()->value,
            'scopeKey' => $timeline->scopeKey(),
            'memoryEnabled' => $timeline->memoryEnabled(),
            'entries' => array_map($this->entryToArray(...), $timeline->entries()->all()),
            'knowledge' => array_map($this->itemToArray(...), $timeline->knowledge()->all()),
            'connections' => array_map(
                static fn (KnowledgeConnection $c): array => [
                    'fromKey' => $c->fromKey(),
                    'toKey' => $c->toKey(),
                    'label' => $c->label(),
                    'reason' => $c->reason(),
                ],
                $timeline->connections()->all(),
            ),
        ];
    }

    public function fromJson(string $json): MemoryTimeline
    {
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidShadowMemoryException('Stored memory timeline is not valid JSON.', 0, $exception);
        }

        if (!is_array($decoded) || !is_string($decoded['id'] ?? null)) {
            throw new InvalidShadowMemoryException('Stored memory timeline is invalid.');
        }

        return new MemoryTimeline(
            new MemoryTimelineId($decoded['id']),
            is_string($decoded['scopeKey'] ?? null) ? $decoded['scopeKey'] : 'default',
            $this->entriesFromArray(is_array($decoded['entries'] ?? null) ? $decoded['entries'] : []),
            $this->knowledgeFromArray(is_array($decoded['knowledge'] ?? null) ? $decoded['knowledge'] : []),
            $this->connectionsFromArray(is_array($decoded['connections'] ?? null) ? $decoded['connections'] : []),
            (bool) ($decoded['memoryEnabled'] ?? true),
        );
    }

    /** @return array<string, mixed> */
    private function entryToArray(MemoryEntry $entry): array
    {
        return [
            'id' => $entry->id(),
            'recordedAt' => $entry->recordedAt()->format(DATE_ATOM),
            'category' => $entry->category()->value,
            'importance' => $entry->importance()->value,
            'confidence' => $entry->confidence()->value,
            'label' => $entry->label(),
            'detail' => $entry->detail(),
            'videoId' => $entry->videoId(),
            'segmentIndex' => $entry->segmentIndex(),
            'sessionId' => $entry->sessionId(),
            'conversationId' => $entry->conversationId(),
            'concepts' => $entry->concepts(),
            'sources' => $entry->sources(),
        ];
    }

    /** @return array<string, mixed> */
    private function itemToArray(KnowledgeItem $item): array
    {
        return [
            'key' => $item->key(),
            'label' => $item->label(),
            'category' => $item->category()->value,
            'progress' => $item->progress()->value,
            'exposureCount' => $item->exposureCount(),
            'questionCount' => $item->questionCount(),
            'challengeSuccessCount' => $item->challengeSuccessCount(),
            'videoIds' => $item->videoIds(),
            'sessionIds' => $item->sessionIds(),
            'explanation' => $item->explanation(),
            'lastSeenAt' => $item->lastSeenAt()?->format(DATE_ATOM),
        ];
    }

    /** @param list<array<string, mixed>> $rows */
    private function entriesFromArray(array $rows): MemoryEntryCollection
    {
        $entries = [];

        foreach ($rows as $row) {
            $category = MemoryCategory::tryFrom((string) ($row['category'] ?? ''));
            $importance = MemoryImportance::tryFrom((string) ($row['importance'] ?? ''));
            $confidence = MemoryConfidence::tryFrom((string) ($row['confidence'] ?? ''));

            if (null === $category || null === $importance || null === $confidence) {
                continue;
            }

            $entries[] = new MemoryEntry(
                (string) ($row['id'] ?? bin2hex(random_bytes(8))),
                \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['recordedAt'] ?? '')) ?: new \DateTimeImmutable(),
                $category,
                $importance,
                $confidence,
                (string) ($row['label'] ?? ''),
                (string) ($row['detail'] ?? ''),
                is_string($row['videoId'] ?? null) ? $row['videoId'] : null,
                is_int($row['segmentIndex'] ?? null) ? $row['segmentIndex'] : null,
                is_string($row['sessionId'] ?? null) ? $row['sessionId'] : null,
                is_string($row['conversationId'] ?? null) ? $row['conversationId'] : null,
                is_array($row['concepts'] ?? null) ? array_values(array_filter($row['concepts'], 'is_string')) : [],
                is_array($row['sources'] ?? null) ? array_values(array_filter($row['sources'], 'is_string')) : [],
            );
        }

        return new MemoryEntryCollection($entries);
    }

    /** @param list<array<string, mixed>> $rows */
    private function knowledgeFromArray(array $rows): KnowledgeItemCollection
    {
        $items = [];

        foreach ($rows as $row) {
            $category = MemoryCategory::tryFrom((string) ($row['category'] ?? ''));
            $progress = KnowledgeProgress::tryFrom((string) ($row['progress'] ?? ''));

            if (null === $category || null === $progress) {
                continue;
            }

            $items[] = new KnowledgeItem(
                (string) ($row['key'] ?? ''),
                (string) ($row['label'] ?? ''),
                $category,
                $progress,
                (int) ($row['exposureCount'] ?? 1),
                (int) ($row['questionCount'] ?? 0),
                (int) ($row['challengeSuccessCount'] ?? 0),
                is_array($row['videoIds'] ?? null) ? array_values(array_filter($row['videoIds'], 'is_string')) : [],
                is_array($row['sessionIds'] ?? null) ? array_values(array_filter($row['sessionIds'], 'is_string')) : [],
                (string) ($row['explanation'] ?? ''),
                \DateTimeImmutable::createFromFormat(DATE_ATOM, (string) ($row['lastSeenAt'] ?? '')) ?: null,
            );
        }

        return new KnowledgeItemCollection($items);
    }

    /** @param list<array<string, mixed>> $rows */
    private function connectionsFromArray(array $rows): KnowledgeConnectionCollection
    {
        $connections = [];

        foreach ($rows as $row) {
            if (!is_string($row['fromKey'] ?? null) || !is_string($row['toKey'] ?? null)) {
                continue;
            }

            $connections[] = new KnowledgeConnection(
                $row['fromKey'],
                $row['toKey'],
                (string) ($row['label'] ?? ''),
                (string) ($row['reason'] ?? ''),
            );
        }

        return new KnowledgeConnectionCollection($connections);
    }
}

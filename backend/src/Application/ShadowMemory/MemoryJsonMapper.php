<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\KnowledgeConnection;
use App\Domain\ShadowMemory\KnowledgeItem;
use App\Domain\ShadowMemory\MemoryEntry;
use App\Domain\ShadowMemory\MemoryTimeline;

final class MemoryJsonMapper
{
    /** @return array<string, mixed> */
    public function toArray(MemoryTimeline $timeline): array
    {
        return [
            'id' => $timeline->id()->value,
            'scopeKey' => $timeline->scopeKey(),
            'memoryEnabled' => $timeline->memoryEnabled(),
            'timeline' => array_map($this->entryToArray(...), $timeline->entries()->all()),
            'concepts' => array_map($this->itemToArray(...), $timeline->knowledge()->byCategory(\App\Domain\ShadowMemory\MemoryCategory::Concept)),
            'vocabulary' => array_map($this->itemToArray(...), $timeline->knowledge()->byCategory(\App\Domain\ShadowMemory\MemoryCategory::Vocabulary)),
            'milestones' => array_map($this->entryToArray(...), $timeline->entries()->byCategory(\App\Domain\ShadowMemory\MemoryCategory::Milestone)),
            'knowledge' => array_map($this->itemToArray(...), $timeline->knowledge()->all()),
            'connections' => array_map($this->connectionToArray(...), $timeline->connections()->all()),
        ];
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
            'progressPercent' => $item->progressPercent(),
            'exposureCount' => $item->exposureCount(),
            'questionCount' => $item->questionCount(),
            'challengeSuccessCount' => $item->challengeSuccessCount(),
            'videoIds' => $item->videoIds(),
            'sessionIds' => $item->sessionIds(),
            'explanation' => $item->explanation(),
            'lastSeenAt' => $item->lastSeenAt()?->format(DATE_ATOM),
        ];
    }

    /** @return array<string, string> */
    private function connectionToArray(KnowledgeConnection $connection): array
    {
        return [
            'fromKey' => $connection->fromKey(),
            'toKey' => $connection->toKey(),
            'label' => $connection->label(),
            'reason' => $connection->reason(),
        ];
    }
}

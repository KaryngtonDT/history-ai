<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\MemoryTimeline;

final class MemorySearchService
{
    public function search(MemoryTimeline $timeline, string $query): array
    {
        $needle = strtolower(trim($query));
        $items = $timeline->knowledge()->search($query);
        $entries = array_values(array_filter(
            $timeline->entries()->all(),
            static fn ($entry): bool => '' === $needle
                || str_contains(strtolower($entry->label()), $needle)
                || str_contains(strtolower($entry->detail()), $needle),
        ));

        return [
            'query' => $query,
            'concepts' => array_map($this->itemToArray(...), $items),
            'entries' => array_map($this->entryToArray(...), $entries),
            'total' => count($items) + count($entries),
        ];
    }

    private function itemToArray(\App\Domain\ShadowMemory\KnowledgeItem $item): array
    {
        return [
            'key' => $item->key(),
            'label' => $item->label(),
            'category' => $item->category()->value,
            'progress' => $item->progress()->value,
            'progressPercent' => $item->progressPercent(),
            'explanation' => $item->explanation(),
            'videoIds' => $item->videoIds(),
        ];
    }

    private function entryToArray(\App\Domain\ShadowMemory\MemoryEntry $entry): array
    {
        return [
            'id' => $entry->id(),
            'label' => $entry->label(),
            'detail' => $entry->detail(),
            'category' => $entry->category()->value,
            'recordedAt' => $entry->recordedAt()->format(DATE_ATOM),
            'videoId' => $entry->videoId(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;

final class KnowledgeMergeEngine
{
    /**
     * @param array<string, list<string>> $duplicateGroups canonical => duplicate keys
     */
    public function merge(KnowledgeCollection $entries, array $duplicateGroups): KnowledgeCollection
    {
        if ([] === $duplicateGroups) {
            return $entries;
        }

        /** @var array<string, KnowledgeEntry> $byKey */
        $byKey = [];

        foreach ($entries->all() as $entry) {
            $byKey[$entry->conceptKey()] = $entry;
        }

        foreach ($duplicateGroups as $canonical => $duplicateKeys) {
            $canonicalEntry = $byKey[$canonical] ?? null;

            if (null === $canonicalEntry) {
                continue;
            }

            foreach ($duplicateKeys as $duplicateKey) {
                $duplicate = $byKey[$duplicateKey] ?? null;

                if (null === $duplicate) {
                    continue;
                }

                $canonicalEntry = $this->mergeEntries($canonicalEntry, $duplicate);
                unset($byKey[$duplicateKey]);
            }

            $byKey[$canonical] = $canonicalEntry;
        }

        $merged = KnowledgeCollection::empty();

        foreach ($byKey as $entry) {
            $merged = $merged->upsert($entry);
        }

        return $merged;
    }

    private function mergeEntries(KnowledgeEntry $primary, KnowledgeEntry $secondary): KnowledgeEntry
    {
        $firstSeenAt = $primary->firstSeenAt() <= $secondary->firstSeenAt()
            ? $primary->firstSeenAt()
            : $secondary->firstSeenAt();
        $lastSeenAt = $primary->lastSeenAt() >= $secondary->lastSeenAt()
            ? $primary->lastSeenAt()
            : $secondary->lastSeenAt();

        return new KnowledgeEntry(
            $primary->id(),
            $primary->conceptKey(),
            $primary->label(),
            '' !== $primary->summary() ? $primary->summary() : $secondary->summary(),
            max($primary->masteryPercent(), $secondary->masteryPercent()),
            $firstSeenAt,
            $lastSeenAt,
            $primary->exposureCount() + $secondary->exposureCount(),
            $primary->exerciseCount() + $secondary->exerciseCount(),
            $primary->explanationCount() + $secondary->explanationCount(),
            array_values(array_unique([...$primary->relatedKeys(), ...$secondary->relatedKeys()])),
            array_values(array_unique([...$primary->recommendations(), ...$secondary->recommendations()])),
        );
    }
}

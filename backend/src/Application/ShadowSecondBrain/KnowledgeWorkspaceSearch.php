<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeCollection;
use App\Domain\ShadowSecondBrain\KnowledgeEntry;

final class KnowledgeWorkspaceSearch
{
    /**
     * @return list<KnowledgeEntry>
     */
    public function search(KnowledgeCollection $entries, string $query): array
    {
        $query = trim($query);

        if ('' === $query) {
            return [];
        }

        $needle = strtolower($query);
        $matches = [];

        foreach ($entries->all() as $entry) {
            if ($this->matches($entry, $needle)) {
                $matches[] = $entry;
            }
        }

        usort(
            $matches,
            static fn (KnowledgeEntry $left, KnowledgeEntry $right): int => $right->masteryPercent() <=> $left->masteryPercent(),
        );

        return $matches;
    }

    private function matches(KnowledgeEntry $entry, string $needle): bool
    {
        $haystacks = [
            strtolower($entry->conceptKey()),
            strtolower($entry->label()),
            strtolower($entry->summary()),
        ];

        foreach ($entry->relatedKeys() as $relatedKey) {
            $haystacks[] = strtolower($relatedKey);
        }

        foreach ($haystacks as $haystack) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}

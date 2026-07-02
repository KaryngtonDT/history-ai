<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeCollection;

final class DuplicateConceptResolver
{
    /**
     * @return array<string, list<string>> canonical key => duplicate keys (excluding canonical)
     */
    public function resolve(KnowledgeCollection $entries): array
    {
        /** @var array<string, list<string>> $groups */
        $groups = [];

        foreach ($entries->all() as $entry) {
            $normalized = $this->normalize($entry->conceptKey());
            $groups[$normalized][] = $entry->conceptKey();
        }

        $duplicates = [];

        foreach ($groups as $normalized => $keys) {
            $unique = array_values(array_unique($keys));

            if (count($unique) <= 1) {
                continue;
            }

            sort($unique);
            $canonical = $unique[0];
            $duplicates[$canonical] = array_values(array_diff($unique, [$canonical]));
        }

        return $duplicates;
    }

    public function normalize(string $conceptKey): string
    {
        $normalized = strtolower(trim($conceptKey));
        $normalized = preg_replace('/[\s\-]+/', '_', $normalized) ?? $normalized;

        return preg_replace('/_+/', '_', $normalized) ?? $normalized;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;

final class KnowledgeDiffEngine
{
    /**
     * @param list<string> $conceptKeys
     *
     * @return array{
     *     resourceType: string,
     *     resourceId: string,
     *     newConcepts: int,
     *     knownConcepts: int,
     *     revisionDue: int,
     *     redundancyPercent: int,
     *     novelConceptKeys: list<string>,
     *     knownConceptKeys: list<string>,
     *     revisionConceptKeys: list<string>
     * }
     */
    public function diff(
        string $resourceType,
        string $resourceId,
        array $conceptKeys,
        KnowledgeWorkspace $workspace,
    ): array {
        $normalizedKeys = array_values(array_unique(array_filter($conceptKeys, static fn ($key) => is_string($key) && '' !== trim($key))));

        $knownKeys = [];
        $novelKeys = [];
        $revisionKeys = [];

        foreach ($normalizedKeys as $key) {
            $entry = $workspace->findEntry($key);

            if (null === $entry) {
                $novelKeys[] = $key;

                continue;
            }

            $knownKeys[] = $key;

            if ($entry->masteryPercent() < 80 && $this->isRevisionDue($entry->lastSeenAt())) {
                $revisionKeys[] = $key;
            }
        }

        $total = count($normalizedKeys);
        $redundancyPercent = 0 === $total
            ? 0
            : (int) round((count($knownKeys) / $total) * 100);

        return [
            'resourceType' => $resourceType,
            'resourceId' => $resourceId,
            'newConcepts' => count($novelKeys),
            'knownConcepts' => count($knownKeys),
            'revisionDue' => count($revisionKeys),
            'redundancyPercent' => $redundancyPercent,
            'novelConceptKeys' => $novelKeys,
            'knownConceptKeys' => $knownKeys,
            'revisionConceptKeys' => $revisionKeys,
        ];
    }

    private function isRevisionDue(\DateTimeImmutable $lastSeenAt): bool
    {
        $threshold = (new \DateTimeImmutable())->modify('-90 days');

        return $lastSeenAt <= $threshold;
    }
}

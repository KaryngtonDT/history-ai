<?php

declare(strict_types=1);

namespace App\Application\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\KnowledgeInsight;
use App\Domain\ShadowSecondBrain\KnowledgeWorkspace;

final class InsightGenerator
{
    /** @return list<KnowledgeInsight> */
    public function generate(KnowledgeWorkspace $workspace): array
    {
        if (!$workspace->workspaceEnabled()) {
            return [];
        }

        $insights = [];
        $now = new \DateTimeImmutable();
        $staleThreshold = $now->modify('-90 days');

        foreach ($workspace->entries()->all() as $entry) {
            if ($entry->lastSeenAt() <= $staleThreshold && $entry->masteryPercent() < 80) {
                $insights[] = new KnowledgeInsight(
                    'revision_'.$entry->conceptKey(),
                    'revision_due',
                    'Revision due: '.$entry->label(),
                    'You learned "'.$entry->label().'" but have not revisited it recently.',
                    $entry->conceptKey(),
                );
            }

            if ($entry->masteryPercent() >= 80 && $entry->exposureCount() >= 3) {
                $insights[] = new KnowledgeInsight(
                    'strength_'.$entry->conceptKey(),
                    'strength',
                    'Strong concept: '.$entry->label(),
                    $entry->label().' appears across '.$entry->exposureCount().' exposures with '.$entry->masteryPercent().'% mastery.',
                    $entry->conceptKey(),
                );
            }
        }

        if ($workspace->statistics()->conceptCount() >= 10) {
            $insights[] = new KnowledgeInsight(
                'coverage_overview',
                'coverage',
                'Knowledge coverage',
                'Your workspace tracks '.$workspace->statistics()->conceptCount().' concepts across multiple sources.',
            );
        }

        return $insights;
    }
}

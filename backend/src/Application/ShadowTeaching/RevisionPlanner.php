<?php

declare(strict_types=1);

namespace App\Application\ShadowTeaching;

use App\Domain\ShadowMemory\KnowledgeProgress;
use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowTeaching\RevisionItem;
use App\Domain\ShadowTeaching\RevisionItemCollection;

final class RevisionPlanner
{
    /** @var list<int> */
    private const INTERVALS = [0, 1, 7, 30];

    public function build(MemoryTimeline $timeline): RevisionItemCollection
    {
        $items = [];
        $now = new \DateTimeImmutable('today');

        foreach ($timeline->knowledge()->all() as $item) {
            if (KnowledgeProgress::Mastered === $item->progress()) {
                continue;
            }

            if ($item->questionCount() < 1 && $item->exposureCount() < 2) {
                continue;
            }

            $interval = self::INTERVALS[min($item->questionCount(), count(self::INTERVALS) - 1)];

            $items[] = new RevisionItem(
                $item->key(),
                $item->label(),
                $now->modify(sprintf('+%d days', $interval)),
                $interval,
                match ($interval) {
                    0 => 'Review today to reinforce recent learning.',
                    1 => 'Short-term reinforcement scheduled for tomorrow.',
                    7 => 'Weekly revision to keep the concept active.',
                    default => 'Long-term retention review.',
                },
            );
        }

        return new RevisionItemCollection($items);
    }
}

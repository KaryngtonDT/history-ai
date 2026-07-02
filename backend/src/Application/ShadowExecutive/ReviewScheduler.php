<?php

declare(strict_types=1);

namespace App\Application\ShadowExecutive;

use App\Domain\ShadowKnowledge\KnowledgeGraph;
use App\Domain\ShadowKnowledge\KnowledgeMastery;

final class ReviewScheduler
{
    private const int STALE_DAYS_THRESHOLD = 28;
    private const int LOW_EXPOSURE_THRESHOLD = 2;

    /** @return list<array{conceptKey: string, label: string, reason: string, masteryPercent: int, exposureCount: int, daysSinceReview: ?int}> */
    public function findStaleConcepts(KnowledgeGraph $graph): array
    {
        $stale = [];

        foreach ($graph->masteries()->all() as $mastery) {
            $reason = $this->staleReason($mastery);

            if (null === $reason) {
                continue;
            }

            $node = $graph->nodes()->find($mastery->nodeKey());
            $stale[] = [
                'conceptKey' => $mastery->nodeKey(),
                'label' => null !== $node ? $node->label() : ucwords(str_replace('_', ' ', $mastery->nodeKey())),
                'reason' => $reason,
                'masteryPercent' => $mastery->percent(),
                'exposureCount' => $mastery->exposureCount(),
                'daysSinceReview' => $this->daysSince($mastery->lastSeenAt()),
            ];
        }

        usort(
            $stale,
            static fn (array $left, array $right): int => ($left['masteryPercent'] <=> $right['masteryPercent'])
                ?: (($left['daysSinceReview'] ?? 0) <=> ($right['daysSinceReview'] ?? 0)),
        );

        return $stale;
    }

    private function staleReason(KnowledgeMastery $mastery): ?string
    {
        if ($mastery->exposureCount() < self::LOW_EXPOSURE_THRESHOLD && $mastery->percent() > 0) {
            return 'Low exposure — concept needs reinforcement.';
        }

        if ($this->isStale($mastery->lastSeenAt()) && !$mastery->mastered()) {
            return sprintf('Last reviewed more than %d days ago.', self::STALE_DAYS_THRESHOLD);
        }

        if ($mastery->percent() > 0 && $mastery->percent() < 40 && $mastery->exposureCount() >= self::LOW_EXPOSURE_THRESHOLD) {
            return 'Mastery is below target — schedule a review.';
        }

        return null;
    }

    private function isStale(?\DateTimeImmutable $lastSeenAt): bool
    {
        if (null === $lastSeenAt) {
            return false;
        }

        $threshold = new \DateTimeImmutable(sprintf('-%d days', self::STALE_DAYS_THRESHOLD));

        return $lastSeenAt < $threshold;
    }

    private function daysSince(?\DateTimeImmutable $lastSeenAt): ?int
    {
        if (null === $lastSeenAt) {
            return null;
        }

        return (int) $lastSeenAt->diff(new \DateTimeImmutable())->days;
    }
}

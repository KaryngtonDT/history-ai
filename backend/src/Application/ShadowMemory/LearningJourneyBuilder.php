<?php

declare(strict_types=1);

namespace App\Application\ShadowMemory;

use App\Domain\ShadowMemory\KnowledgeProgress;
use App\Domain\ShadowMemory\MemoryTimeline;

final class LearningJourneyBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(MemoryTimeline $timeline): array
    {
        $items = $timeline->knowledge()->all();
        usort($items, static fn ($a, $b) => $b->progressPercent() <=> $a->progressPercent());

        $today = null !== $items[0] ? [
            'label' => 'Understand '.$items[0]->label(),
            'progressPercent' => $items[0]->progressPercent(),
        ] : null;

        $next = $this->findNextStep($timeline);
        $review = $this->findReviewStep($timeline);
        $longTerm = $this->findLongTermStep($timeline);

        return [
            'today' => $today,
            'nextStep' => $next,
            'preparation' => $review,
            'longTerm' => $longTerm,
        ];
    }

    private function findNextStep(MemoryTimeline $timeline): ?array
    {
        foreach ($timeline->knowledge()->all() as $item) {
            if (KnowledgeProgress::Learning === $item->progress() || KnowledgeProgress::New === $item->progress()) {
                return ['label' => $item->label(), 'key' => $item->key()];
            }
        }

        foreach ($timeline->connections()->all() as $connection) {
            return ['label' => $connection->toKey(), 'reason' => $connection->label()];
        }

        return null;
    }

    private function findReviewStep(MemoryTimeline $timeline): ?array
    {
        foreach ($timeline->knowledge()->all() as $item) {
            if ($item->questionCount() > 1 && $item->progressPercent() < 80) {
                return ['label' => 'Review '.$item->label(), 'key' => $item->key()];
            }
        }

        return null;
    }

    private function findLongTermStep(MemoryTimeline $timeline): ?array
    {
        if (null !== $timeline->knowledge()->find('kubernetes')) {
            return ['label' => 'Deploy a GPU cluster', 'key' => 'gpu_cluster'];
        }

        if (null !== $timeline->knowledge()->find('docker')) {
            return ['label' => 'Move from Docker to Kubernetes', 'key' => 'kubernetes'];
        }

        return null;
    }
}

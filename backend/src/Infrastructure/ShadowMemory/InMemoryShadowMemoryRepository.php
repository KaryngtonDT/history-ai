<?php

declare(strict_types=1);

namespace App\Infrastructure\ShadowMemory;

use App\Domain\ShadowMemory\MemoryTimeline;
use App\Domain\ShadowMemory\MemoryTimelineId;
use App\Domain\ShadowMemory\ShadowMemoryRepositoryInterface;

final class InMemoryShadowMemoryRepository implements ShadowMemoryRepositoryInterface
{
    /** @var array<string, MemoryTimeline> */
    private array $timelines = [];

    public function findByScope(string $scopeKey): ?MemoryTimeline
    {
        foreach ($this->timelines as $timeline) {
            if ($timeline->scopeKey() === $scopeKey) {
                return $timeline;
            }
        }

        return null;
    }

    public function findById(MemoryTimelineId $id): ?MemoryTimeline
    {
        return $this->timelines[$id->value] ?? null;
    }

    public function save(MemoryTimeline $timeline): void
    {
        $this->timelines[$timeline->id()->value] = $timeline;
    }
}

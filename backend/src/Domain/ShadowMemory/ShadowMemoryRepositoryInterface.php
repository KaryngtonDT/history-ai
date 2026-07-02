<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

interface ShadowMemoryRepositoryInterface
{
    public function findByScope(string $scopeKey): ?MemoryTimeline;

    public function findById(MemoryTimelineId $id): ?MemoryTimeline;

    public function save(MemoryTimeline $timeline): void;
}

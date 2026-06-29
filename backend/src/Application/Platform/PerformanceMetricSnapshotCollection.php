<?php

declare(strict_types=1);

namespace App\Application\Platform;

final readonly class PerformanceMetricSnapshotCollection
{
    /** @var list<PerformanceMetricSnapshot> */
    private array $snapshots;

    /**
     * Snapshots are ordered newest first.
     *
     * @param list<PerformanceMetricSnapshot> $snapshots
     */
    public function __construct(array $snapshots = [])
    {
        $this->snapshots = array_values($snapshots);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<PerformanceMetricSnapshot>
     */
    public function snapshots(): array
    {
        return $this->snapshots;
    }

    public function count(): int
    {
        return count($this->snapshots);
    }
}

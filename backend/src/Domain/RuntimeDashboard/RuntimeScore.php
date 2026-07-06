<?php

declare(strict_types=1);

namespace App\Domain\RuntimeDashboard;

final readonly class RuntimeScore
{
    /**
     * @param list<RuntimeScoreBreakdown> $breakdown
     */
    public function __construct(
        public float $score,
        public string $grade,
        public string $summary,
        public array $breakdown,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'grade' => $this->grade,
            'summary' => $this->summary,
            'breakdown' => array_map(
                static fn (RuntimeScoreBreakdown $item): array => $item->toArray(),
                $this->breakdown,
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Runtime;

final readonly class RuntimeHealth
{
    /**
     * @param list<string> $issues
     */
    public function __construct(
        public RuntimeStatus $status,
        public float $score,
        public int $healthyEngines,
        public int $totalEngines,
        public array $issues = [],
        public ?string $lastCheckedAt = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'score' => $this->score,
            'healthyEngines' => $this->healthyEngines,
            'totalEngines' => $this->totalEngines,
            'issues' => $this->issues,
            'lastCheckedAt' => $this->lastCheckedAt,
        ];
    }
}

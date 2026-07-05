<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Health;

use App\Domain\Runtime\RuntimeHealth;
use App\Domain\Runtime\RuntimeStatus;
use App\Infrastructure\Runtime\Readiness\ReadinessEngine;

final class HealthMonitor
{
    /** @var list<array{engineId: string, message: string, at: string}> */
    private array $failureHistory = [];

    public function __construct(private readonly ReadinessEngine $readinessEngine)
    {
    }

    public function heartbeat(): RuntimeHealth
    {
        $report = $this->readinessEngine->evaluate();
        $score = 0 === $report->totalCount
            ? 0.0
            : round(($report->readyCount / $report->totalCount) * 100, 1);

        return new RuntimeHealth(
            status: $report->status,
            score: $score,
            healthyEngines: $report->readyCount,
            totalEngines: $report->totalCount,
            issues: $report->issues,
            lastCheckedAt: (new \DateTimeImmutable())->format(DATE_ATOM),
        );
    }

    public function recordFailure(string $engineId, string $message): void
    {
        $this->failureHistory[] = [
            'engineId' => $engineId,
            'message' => $message,
            'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        if (count($this->failureHistory) > 100) {
            $this->failureHistory = array_slice($this->failureHistory, -100);
        }
    }

    /**
     * @return list<array{engineId: string, message: string, at: string}>
     */
    public function failureHistory(): array
    {
        return $this->failureHistory;
    }
}

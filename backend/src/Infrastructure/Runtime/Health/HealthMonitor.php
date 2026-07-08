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

    public function __construct(
        private readonly ReadinessEngine $readinessEngine,
        private readonly RuntimePlatformHealthService $platformHealthService,
    ) {
    }

    public function heartbeat(): RuntimeHealth
    {
        $report = $this->readinessEngine->evaluate();
        $platformHealth = $this->platformHealthService->evaluate();
        $coreHealth = is_array($platformHealth['coreHealth'] ?? null) ? $platformHealth['coreHealth'] : [];
        $score = (float) ($coreHealth['percent'] ?? 0.0);
        $coreReady = 'ready' === ($coreHealth['status'] ?? 'fail');

        $issues = array_values(array_filter(
            $report->issues,
            static function (string $issue): bool {
                foreach (['OCR', 'Vision', 'Embeddings', 'Reranking', 'LatentSync', 'Lip Sync', 'Premium'] as $optionalMarker) {
                    if (str_contains($issue, $optionalMarker)) {
                        return false;
                    }
                }

                return true;
            },
        ));

        return new RuntimeHealth(
            status: $coreReady ? RuntimeStatus::Ready : RuntimeStatus::Degraded,
            score: $score,
            healthyEngines: (int) ($coreHealth['readyCount'] ?? 0),
            totalEngines: (int) ($coreHealth['totalCount'] ?? 0),
            issues: $issues,
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

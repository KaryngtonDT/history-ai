<?php

declare(strict_types=1);

namespace App\Application\Mobile;

use App\Application\Platform\PlatformHealthCheckerInterface;

final class MobileServerBuilder
{
    public function __construct(
        private readonly PlatformHealthCheckerInterface $healthChecker,
    ) {
    }

    /** @return array<string, mixed> */
    public function build(): array
    {
        $production = $this->healthChecker->productionReadiness();
        $live = $this->healthChecker->live();

        $checks = is_array($production['checks'] ?? null) ? $production['checks'] : [];
        $healthyCount = 0;

        foreach ($checks as $check) {
            if (is_array($check) && ($check['ok'] ?? false)) {
                ++$healthyCount;
            }
        }

        return [
            'status' => $production['status'] ?? 'unknown',
            'liveStatus' => $live['status'] ?? 'unknown',
            'checks' => $checks,
            'healthyCount' => $healthyCount,
            'totalChecks' => count($checks),
            'available' => 'ready' === ($live['status'] ?? '') || 'live' === ($live['status'] ?? ''),
        ];
    }
}

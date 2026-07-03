<?php

declare(strict_types=1);

namespace App\Application\Mobile;

use App\Application\Platform\PlatformHealthCheckerInterface;

final class MobileHealthBuilder
{
    public function __construct(
        private readonly PlatformHealthCheckerInterface $healthChecker,
    ) {
    }

    /** @return array<string, mixed> */
    public function build(): array
    {
        $readiness = $this->healthChecker->readiness();
        $live = $this->healthChecker->live();

        return [
            'status' => $readiness['status'] ?? 'unknown',
            'live' => $live['status'] ?? 'unknown',
            'checks' => $readiness['checks'] ?? [],
            'liveChecks' => $live['checks'] ?? [],
        ];
    }
}

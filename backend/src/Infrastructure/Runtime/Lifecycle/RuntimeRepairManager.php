<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Lifecycle;

use App\Infrastructure\Runtime\Benchmark\BenchmarkRunner;

final class RuntimeRepairManager
{
    public function __construct(
        private readonly RuntimeProvisionManager $provisionManager,
        private readonly BenchmarkRunner $benchmarkRunner,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function repair(string $engineId): array
    {
        $provision = $this->provisionManager->install($engineId);
        $test = $this->benchmarkRunner->runEngine($engineId);

        return [
            'action' => 'repair',
            'engineId' => $engineId,
            'provision' => $provision,
            'validation' => $test,
            'ok' => ($provision['ok'] ?? false) && ($test['ok'] ?? false),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Telemetry\DTO;

final readonly class ProviderStatResult
{
    public function __construct(
        public string $stage,
        public string $providerId,
        public int $invocationCount,
        public float $averageDurationSeconds,
    ) {
    }
}

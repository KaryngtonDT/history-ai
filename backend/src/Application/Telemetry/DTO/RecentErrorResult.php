<?php

declare(strict_types=1);

namespace App\Application\Telemetry\DTO;

final readonly class RecentErrorResult
{
    public function __construct(
        public string $message,
        public string $status,
        public string $recordedAt,
    ) {
    }
}

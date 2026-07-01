<?php

declare(strict_types=1);

namespace App\Application\Telemetry\DTO;

final readonly class PipelineTelemetryResult
{
    /**
     * @param list<array{type: string, value: float, unit: string}> $metrics
     * @param list<array{stage: string, providerId: string, invocationCount: int, totalDurationSeconds: float}> $providerUsages
     */
    public function __construct(
        public string $id,
        public string $workspaceId,
        public string $videoId,
        public bool $success,
        public array $metrics,
        public array $providerUsages,
        public string $recordedAt,
        public ?string $batchJobId,
        public ?int $qualityScore,
        public ?string $errorMessage,
    ) {
    }
}

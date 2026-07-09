<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;

final class PipelineStageProgressReporter
{
    public function __construct(
        private readonly PipelineProgressService $progressService,
        private readonly PipelineJobId $jobId,
        private readonly PipelineStageType $stage,
    ) {
    }

    /**
     * @param array<string, mixed> $detail
     */
    public function checkpoint(
        string $checkpoint,
        int $progressPercent,
        ?string $currentStep = null,
        ?array $detail = null,
    ): void {
        $step = $currentStep ?? $checkpoint;
        $resolved = PipelineStageCheckpointRegistry::resolve($this->stage, $step);
        $progress = max($resolved['minPercent'], min($resolved['maxPercent'], $progressPercent));

        $this->progressService->updateProgressDetailed(
            $this->jobId,
            $progress,
            $step,
            null,
            array_merge(['checkpoint' => $checkpoint], $detail ?? []),
        );
    }

    public function heartbeat(): void
    {
        $this->progressService->heartbeat($this->jobId, $this->stage);
    }
}

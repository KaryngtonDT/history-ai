<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;

interface PipelineJobRepositoryInterface
{
    public function save(PipelineJob $job): void;

    public function findById(PipelineJobId $jobId): ?PipelineJob;

    public function findActiveBySourceAndStage(string $sourceId, PipelineStageType $stage): ?PipelineJob;

    /** @return list<PipelineJob> */
    public function findBySourceId(string $sourceId): array;

    /** @return list<PipelineJob> */
    public function findActiveBySourceId(string $sourceId): array;
}

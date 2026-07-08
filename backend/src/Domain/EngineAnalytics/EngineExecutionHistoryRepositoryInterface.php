<?php

declare(strict_types=1);

namespace App\Domain\EngineAnalytics;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;

interface EngineExecutionHistoryRepositoryInterface
{
    public function record(EngineExecutionHistory $execution): void;

    public function findById(EngineExecutionHistoryId $executionId): ?EngineExecutionHistory;

    public function findLatestByPipelineJobId(PipelineJobId $pipelineJobId): ?EngineExecutionHistory;

    /**
     * @return list<EngineExecutionHistory>
     */
    public function findRecent(
        ?PipelineStageType $stage = null,
        ?string $engineId = null,
        ?string $hardwareProfile = null,
        int $limit = 20,
    ): array;

    /**
     * @return list<EngineExecutionHistory>
     */
    public function findByEngineId(string $engineId, int $limit = 50): array;
}

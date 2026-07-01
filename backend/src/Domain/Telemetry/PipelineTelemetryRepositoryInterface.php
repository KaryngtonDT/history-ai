<?php

declare(strict_types=1);

namespace App\Domain\Telemetry;

interface PipelineTelemetryRepositoryInterface
{
    public function append(PipelineTelemetry $telemetry): void;

    /**
     * @return list<PipelineTelemetry>
     */
    public function findByWorkspaceId(string $workspaceId): array;
}

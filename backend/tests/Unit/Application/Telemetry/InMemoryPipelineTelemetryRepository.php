<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Telemetry;

use App\Domain\Telemetry\PipelineTelemetry;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;

final class InMemoryPipelineTelemetryRepository implements PipelineTelemetryRepositoryInterface
{
    /** @var list<PipelineTelemetry> */
    private array $records = [];

    public function append(PipelineTelemetry $telemetry): void
    {
        $this->records[] = $telemetry;
    }

    public function findByWorkspaceId(string $workspaceId): array
    {
        return array_values(array_filter(
            $this->records,
            static fn (PipelineTelemetry $record): bool => $record->workspaceId() === $workspaceId,
        ));
    }
}

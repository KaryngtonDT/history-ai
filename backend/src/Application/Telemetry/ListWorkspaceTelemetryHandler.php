<?php

declare(strict_types=1);

namespace App\Application\Telemetry;

use App\Application\Telemetry\DTO\PipelineTelemetryResult;
use App\Application\Telemetry\Queries\ListWorkspaceTelemetryQuery;
use App\Domain\Telemetry\PipelineTelemetryRepositoryInterface;

final class ListWorkspaceTelemetryHandler
{
    public function __construct(
        private readonly PipelineTelemetryRepositoryInterface $telemetryRepository,
    ) {
    }

    /**
     * @return list<PipelineTelemetryResult>
     */
    public function __invoke(ListWorkspaceTelemetryQuery $query): array
    {
        $records = $this->telemetryRepository->findByWorkspaceId($query->workspaceId);

        return array_map(
            static fn ($record): PipelineTelemetryResult => PipelineTelemetryResultMapper::fromDomain($record),
            $records,
        );
    }
}

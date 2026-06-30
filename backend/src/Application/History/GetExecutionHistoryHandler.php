<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Application\History\DTO\ExecutionHistoryResult;
use App\Application\History\DTO\ExecutionVersionResult;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;
use App\Application\History\Queries\GetExecutionHistoryQuery;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionHistoryRepositoryInterface;
use App\Domain\Video\VideoId;

final class GetExecutionHistoryHandler
{
    public function __construct(
        private readonly ExecutionHistoryRepositoryInterface $historyRepository,
        private readonly ExecutionHistorySnapshotStoreInterface $snapshotStore,
    ) {
    }

    public function __invoke(GetExecutionHistoryQuery $query): ExecutionHistoryResult
    {
        $videoId = new VideoId($query->videoId);
        $history = $this->historyRepository->findByVideoId($videoId);

        if (null === $history) {
            throw new InvalidExecutionHistoryException('Execution history not found.');
        }

        $snapshots = $this->snapshotStore->findAllByVideoId($videoId);
        $versions = array_map(
            static fn ($snapshot): ExecutionVersionResult => new ExecutionVersionResult(
                $snapshot->version->versionNumber(),
                $snapshot->version->pipelineConfigurationId()->value,
                $snapshot->version->optimizationId()->value,
                $snapshot->version->qualityReportId()->value,
                $snapshot->version->renderedVideoId()->value,
                $snapshot->version->createdAt()->format(\DateTimeInterface::ATOM),
                is_string($snapshot->optimization['profile'] ?? null) ? $snapshot->optimization['profile'] : 'unknown',
                is_int($snapshot->qualityReport['overallScore'] ?? null) ? $snapshot->qualityReport['overallScore'] : 0,
            ),
            $snapshots,
        );

        return new ExecutionHistoryResult(
            $history->id()->value,
            $history->videoId()->value,
            $versions,
        );
    }
}

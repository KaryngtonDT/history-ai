<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Application\History\Commands\RecordExecutionHistoryCommand;
use App\Application\History\DTO\ExecutionVersionResult;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;
use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Application\Quality\QualityReportJsonMapper;
use App\Domain\History\ExecutionHistoryRepositoryInterface;
use App\Domain\History\ExecutionSnapshot;

final class RecordExecutionHistoryHandler
{
    public function __construct(
        private readonly ExecutionHistoryRepositoryInterface $historyRepository,
        private readonly ExecutionHistorySnapshotStoreInterface $snapshotStore,
        private readonly PipelineConfigurationJsonMapper $pipelineMapper,
        private readonly ExecutionOptimizationSnapshotMapper $optimizationMapper,
        private readonly QualityReportJsonMapper $qualityMapper,
    ) {
    }

    public function __invoke(RecordExecutionHistoryCommand $command): ExecutionVersionResult
    {
        $history = $this->historyRepository->findOrCreateForVideo($command->videoId);
        $snapshot = ExecutionSnapshot::create(
            $command->pipelineConfiguration->id(),
            $command->optimization->id(),
            $command->qualityReport->id(),
            $command->renderedVideoId,
        );
        $updatedHistory = $history->appendSnapshot($snapshot);
        $version = $updatedHistory->latest();
        \assert(null !== $version);

        $versionSnapshot = ExecutionVersionSnapshot::fromCompletedRender(
            $version,
            $command->pipelineConfiguration,
            $command->optimization,
            $command->qualityReport,
            $this->pipelineMapper,
            $this->optimizationMapper,
            $this->qualityMapper,
        );

        $this->snapshotStore->append($updatedHistory, $versionSnapshot);
        $this->historyRepository->save($updatedHistory);

        return new ExecutionVersionResult(
            $version->versionNumber(),
            $version->pipelineConfigurationId()->value,
            $version->optimizationId()->value,
            $version->qualityReportId()->value,
            $version->renderedVideoId()->value,
            $version->createdAt()->format(\DateTimeInterface::ATOM),
            $command->optimization->profile()->value,
            $command->qualityReport->overallScore()->value(),
        );
    }
}

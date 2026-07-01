<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\History\Commands\RecordExecutionHistoryCommand;
use App\Application\History\Commands\ReprocessExecutionCommand;
use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\History\ExecutionReplayContextInterface;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Video\VideoId;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;

final class ReprocessExecutionHandler
{
    public function __construct(
        private readonly ExecutionHistorySnapshotStoreInterface $snapshotStore,
        private readonly ExecutionReplayContextInterface $replayContext,
        private readonly PipelineConfigurationJsonMapper $pipelineMapper,
        private readonly VideoProcessingQueueInterface $videoProcessingQueue,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(ReprocessExecutionCommand $command): void
    {
        $this->authorizationGuard->assertVideoAction(
            $command->videoId,
            $command->actorUserId,
            WorkspaceAction::Reprocess,
        );

        $videoId = new VideoId($command->videoId);
        $snapshot = $this->snapshotStore->findByVideoIdAndVersion($videoId, $command->versionNumber);

        if (null === $snapshot) {
            throw new InvalidExecutionHistoryException('Execution version not found.');
        }

        $configuration = $this->cloneConfiguration($snapshot->pipelineConfiguration);
        $configuration = $this->applyOverrides($configuration, $command->providerOverrides);

        $this->replayContext->arm($videoId, $configuration);

        $batchJobId = null !== $command->batchJobId
            ? new BatchJobId($command->batchJobId)
            : null;

        $this->videoProcessingQueue->enqueue(
            $videoId,
            ProcessingMode::Manual,
            null,
            $batchJobId,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function cloneConfiguration(array $payload): PipelineConfiguration
    {
        $payload['id'] = PipelineConfigurationId::generate()->value;

        return $this->pipelineMapper->fromJson(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @param array<string, string> $overrides
     */
    private function applyOverrides(PipelineConfiguration $configuration, array $overrides): PipelineConfiguration
    {
        $updated = $configuration;

        foreach ($overrides as $stage => $providerId) {
            $stageType = PipelineStageType::tryFrom($stage);

            if (null === $stageType || '' === trim($providerId)) {
                continue;
            }

            $updated = $updated->replace($stageType, $providerId);
        }

        return $updated;
    }
}

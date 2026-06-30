<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Application\Workspace\Commands\RunBatchProcessingCommand;
use App\Application\Workspace\DTO\RunBatchProcessingResult;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobRepositoryInterface;
use App\Domain\Workspace\BatchJobStatus;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectRepositoryInterface;
use Throwable;

final class RunBatchProcessingHandler
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly BatchJobRepositoryInterface $batchJobRepository,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoProcessingQueueInterface $videoProcessingQueue,
    ) {
    }

    public function __invoke(RunBatchProcessingCommand $command): RunBatchProcessingResult
    {
        $projectId = new ProjectId($command->projectId);
        $project = $this->projectRepository->findById($projectId);

        if (null === $project) {
            throw new InvalidProjectException('Project not found.');
        }

        if ($project->videos()->isEmpty()) {
            throw new InvalidProjectException('Project has no videos to process.');
        }

        $batchJob = BatchJob::create(
            BatchJobId::generate(),
            $projectId,
            $project->videos()->videoIds(),
            $command->targetLanguages,
        )->start();

        $this->batchJobRepository->save($batchJob);

        $queuedVideos = 0;
        $failedVideoIds = [];

        foreach ($project->videos()->all() as $projectVideo) {
            try {
                $videoId = $projectVideo->videoId();

                if (null === $this->videoRepository->findById($videoId)) {
                    $failedVideoIds[] = $videoId->value;
                    continue;
                }

                $this->videoProcessingQueue->enqueue(
                    $videoId,
                    $command->processingMode,
                    $command->strategy,
                    $batchJob->id(),
                );
                ++$queuedVideos;
            } catch (Throwable) {
                $failedVideoIds[] = $projectVideo->videoId()->value;
            }
        }

        $batchJob = $this->resolveBatchStatus($batchJob, $queuedVideos, count($failedVideoIds));
        $this->batchJobRepository->save($batchJob);

        return RunBatchProcessingResult::fromBatchJob($batchJob, $queuedVideos, $failedVideoIds);
    }

    private function resolveBatchStatus(BatchJob $batchJob, int $queuedVideos, int $failedVideos): BatchJob
    {
        if (0 === $queuedVideos) {
            return $batchJob->withStatus(BatchJobStatus::Failed, $batchJob->progress());
        }

        if ($failedVideos > 0 && $queuedVideos > 0) {
            return $batchJob;
        }

        return $batchJob;
    }
}

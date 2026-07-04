<?php

declare(strict_types=1);

namespace App\Application\Video;

use App\Application\Video\Ports\VideoProcessingQueueInterface;
use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\Video\VideoStatus;

final class VideoProcessingEnqueueService
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VideoProcessingQueueInterface $videoProcessingQueue,
    ) {
    }

    public function enqueueIfNeeded(VideoId $videoId, ProcessingMode $processingMode = ProcessingMode::Manual): bool
    {
        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            return false;
        }

        $status = $job->status();

        if (VideoStatus::Processing === $status) {
            return false;
        }

        if (VideoStatus::Failed === $status) {
            $job = $job->requeue();
            $this->videoRepository->save($job);
        }

        if (VideoStatus::Uploaded === $status && null !== $job->storagePath()) {
            $job = $job->queue();
            $this->videoRepository->save($job);
        }

        if (VideoStatus::Queued !== $job->status()) {
            return false;
        }

        $this->videoProcessingQueue->enqueue($videoId, $processingMode);

        return true;
    }
}

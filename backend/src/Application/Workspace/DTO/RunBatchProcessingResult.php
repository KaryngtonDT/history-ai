<?php

declare(strict_types=1);

namespace App\Application\Workspace\DTO;

use App\Domain\Workspace\BatchJob;

final readonly class RunBatchProcessingResult
{
    /**
     * @param list<string> $targetLanguages
     * @param list<string> $failedVideoIds
     */
    public function __construct(
        public string $batchJobId,
        public string $projectId,
        public string $status,
        public int $progress,
        public int $totalVideos,
        public int $queuedVideos,
        public array $targetLanguages,
        public array $failedVideoIds = [],
    ) {
    }

    public static function fromBatchJob(BatchJob $batchJob, int $queuedVideos, array $failedVideoIds = []): self
    {
        return new self(
            $batchJob->id()->value,
            $batchJob->projectId()->value,
            $batchJob->status()->value,
            $batchJob->progress()->percentage(),
            $batchJob->totalVideos(),
            $queuedVideos,
            $batchJob->targetLanguages(),
            $failedVideoIds,
        );
    }
}

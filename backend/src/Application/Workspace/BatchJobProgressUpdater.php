<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobRepositoryInterface;

final class BatchJobProgressUpdater
{
    public function __construct(
        private readonly BatchJobRepositoryInterface $batchJobRepository,
    ) {
    }

    public function recordOutcome(?string $batchJobId, VideoId $videoId, bool $success): void
    {
        if (null === $batchJobId || '' === trim($batchJobId)) {
            return;
        }

        try {
            $this->batchJobRepository->recordVideoOutcome(
                new BatchJobId($batchJobId),
                $videoId,
                $success,
            );
        } catch (\Throwable) {
        }
    }
}

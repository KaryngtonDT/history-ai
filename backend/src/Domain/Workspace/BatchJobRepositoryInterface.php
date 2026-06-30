<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

use App\Domain\Video\VideoId;

interface BatchJobRepositoryInterface
{
    public function save(BatchJob $batchJob): void;

    public function findById(BatchJobId $id): ?BatchJob;

    public function findLatestByProjectId(ProjectId $projectId): ?BatchJob;

    public function recordVideoOutcome(BatchJobId $batchJobId, VideoId $videoId, bool $success): ?BatchJob;
}

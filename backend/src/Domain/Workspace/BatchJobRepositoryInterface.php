<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

interface BatchJobRepositoryInterface
{
    public function save(BatchJob $batchJob): void;

    public function findById(BatchJobId $id): ?BatchJob;

    public function findLatestByProjectId(ProjectId $projectId): ?BatchJob;
}

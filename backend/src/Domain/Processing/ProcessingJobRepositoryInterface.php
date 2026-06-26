<?php

declare(strict_types=1);

namespace App\Domain\Processing;

interface ProcessingJobRepositoryInterface
{
    public function save(ProcessingJob $job): void;

    public function findById(ProcessingJobId $id): ?ProcessingJob;
}

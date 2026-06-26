<?php

declare(strict_types=1);

namespace App\Application\Processing\DTO;

use App\Domain\Processing\ProcessingJobId;
use App\Domain\Processing\ProcessingJobStatus;

final readonly class CreateProcessingJobResult
{
    public function __construct(
        public ProcessingJobId $processingJobId,
        public ProcessingJobStatus $status,
        public int $progress,
    ) {
    }
}

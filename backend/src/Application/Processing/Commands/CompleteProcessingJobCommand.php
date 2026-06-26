<?php

declare(strict_types=1);

namespace App\Application\Processing\Commands;

final readonly class CompleteProcessingJobCommand
{
    public function __construct(
        public string $processingJobId,
    ) {
    }
}

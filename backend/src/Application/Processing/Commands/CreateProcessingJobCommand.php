<?php

declare(strict_types=1);

namespace App\Application\Processing\Commands;

use App\Domain\Processing\ProcessingJobType;

final readonly class CreateProcessingJobCommand
{
    public function __construct(
        public string $contentId,
        public ProcessingJobType $type,
    ) {
    }
}

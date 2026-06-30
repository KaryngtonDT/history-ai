<?php

declare(strict_types=1);

namespace App\Application\Video\Commands;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class UploadVideoCommand
{
    public function __construct(
        public string $originalFilename,
        public int $fileSizeBytes,
        public string $temporaryPath,
        public ProcessingMode $processingMode = ProcessingMode::Manual,
        public ?ProcessingStrategy $strategy = null,
    ) {
    }
}

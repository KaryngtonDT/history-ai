<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Commands;

use App\Domain\Orchestrator\ProcessingMode;
use App\Domain\Orchestrator\ProcessingStrategy;

final readonly class UploadAudioCommand
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

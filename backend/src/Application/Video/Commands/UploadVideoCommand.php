<?php

declare(strict_types=1);

namespace App\Application\Video\Commands;

final readonly class UploadVideoCommand
{
    public function __construct(
        public string $originalFilename,
        public int $fileSizeBytes,
        public string $temporaryPath,
    ) {
    }
}

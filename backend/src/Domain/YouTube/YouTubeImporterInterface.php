<?php

declare(strict_types=1);

namespace App\Domain\YouTube;

final readonly class YouTubeDownloadResult
{
    public function __construct(
        public string $filePath,
        public string $originalFilename,
        public YouTubeMetadata $metadata,
    ) {
    }
}

interface YouTubeImporterInterface
{
    public function fetchMetadata(string $url): YouTubeMetadata;

    public function download(string $url, string $targetDirectory): YouTubeDownloadResult;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\YouTube;

use App\Domain\YouTube\Exception\YouTubeImporterException;
use App\Domain\YouTube\YouTubeDownloadResult;
use App\Domain\YouTube\YouTubeImporterInterface;
use App\Domain\YouTube\YouTubeMetadata;
use App\Domain\YouTube\YouTubeUrl;

final class MockYouTubeImporter implements YouTubeImporterInterface
{
    public function fetchMetadata(string $url): YouTubeMetadata
    {
        YouTubeUrl::assertValid($url);

        return new YouTubeMetadata(
            title: 'Mock YouTube Lecture',
            durationSeconds: 180,
            thumbnailUrl: 'https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg',
            language: 'en',
            channelName: 'History AI Mock Channel',
        );
    }

    public function download(string $url, string $targetDirectory): YouTubeDownloadResult
    {
        YouTubeUrl::assertValid($url);

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new YouTubeImporterException('Unable to create YouTube download directory.');
        }

        $filePath = rtrim($targetDirectory, '/\\').'/mock-youtube-import.mp4';
        file_put_contents($filePath, str_repeat('a', 256));

        $metadata = $this->fetchMetadata($url);

        return new YouTubeDownloadResult(
            filePath: $filePath,
            originalFilename: 'mock-youtube-lecture.mp4',
            metadata: $metadata,
        );
    }
}

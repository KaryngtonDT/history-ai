<?php

declare(strict_types=1);

namespace App\Infrastructure\YouTube;

use App\Domain\YouTube\Exception\YouTubeImporterException;
use App\Domain\YouTube\YouTubeDownloadResult;
use App\Domain\YouTube\YouTubeImporterInterface;
use App\Domain\YouTube\YouTubeMetadata;
use App\Domain\YouTube\YouTubeUrl;
use Symfony\Component\Process\Process;

final class YtDlpYouTubeImporter implements YouTubeImporterInterface
{
    public function __construct(
        private readonly string $binary,
    ) {
    }

    public function fetchMetadata(string $url): YouTubeMetadata
    {
        YouTubeUrl::assertValid($url);

        $process = new Process([
            $this->binary,
            '--dump-json',
            '--skip-download',
            $url,
        ]);

        $process->setTimeout(120);
        $process->mustRun();

        return $this->parseMetadata($process->getOutput());
    }

    public function download(string $url, string $targetDirectory): YouTubeDownloadResult
    {
        YouTubeUrl::assertValid($url);

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new YouTubeImporterException('Unable to create YouTube download directory.');
        }

        $outputTemplate = rtrim($targetDirectory, '/\\').'/%(id)s.%(ext)s';

        $process = new Process([
            $this->binary,
            '--format',
            'mp4/best',
            '--merge-output-format',
            'mp4',
            '--output',
            $outputTemplate,
            '--print',
            'after_move:filepath',
            $url,
        ]);

        $process->setTimeout(600);
        $process->mustRun();

        $filePath = trim($process->getOutput());

        if ('' === $filePath || !is_file($filePath)) {
            throw new YouTubeImporterException('YouTube download did not produce a video file.');
        }

        $metadata = $this->fetchMetadata($url);

        return new YouTubeDownloadResult(
            filePath: $filePath,
            originalFilename: basename($filePath),
            metadata: $metadata,
        );
    }

    private function parseMetadata(string $json): YouTubeMetadata
    {
        /** @var array<string, mixed>|null $data */
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new YouTubeImporterException('Unable to parse YouTube metadata.');
        }

        $title = is_string($data['title'] ?? null) ? $data['title'] : 'YouTube Video';
        $duration = is_numeric($data['duration'] ?? null) ? (int) $data['duration'] : null;
        $thumbnail = is_string($data['thumbnail'] ?? null) ? $data['thumbnail'] : null;
        $language = is_string($data['language'] ?? null) ? $data['language'] : null;
        $channel = is_string($data['channel'] ?? null) ? $data['channel'] : null;

        return new YouTubeMetadata(
            title: $title,
            durationSeconds: $duration,
            thumbnailUrl: $thumbnail,
            language: $language,
            channelName: $channel,
        );
    }
}

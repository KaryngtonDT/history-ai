<?php

declare(strict_types=1);

namespace App\Infrastructure\Video;

use App\Application\Video\Ports\VideoStorageInterface;
use App\Domain\Video\VideoExtension;
use App\Domain\Video\VideoId;
use RuntimeException;

final class LocalVideoStorage implements VideoStorageInterface
{
    public function __construct(
        private readonly string $storageDirectory,
    ) {
    }

    public function store(VideoId $videoId, string $sourcePath, string $originalFilename): string
    {
        if (!is_file($sourcePath)) {
            throw new RuntimeException('Video upload source file is missing.');
        }

        $extension = VideoExtension::fromFilename($originalFilename);
        $this->ensureStorageDirectoryExists();

        $destinationPath = sprintf(
            '%s/%s.%s',
            rtrim($this->storageDirectory, '/\\'),
            $videoId->value,
            $extension->value,
        );

        if (!@rename($sourcePath, $destinationPath)) {
            if (!@copy($sourcePath, $destinationPath)) {
                throw new RuntimeException('Unable to move uploaded video into storage.');
            }

            @unlink($sourcePath);
        }

        return $destinationPath;
    }

    private function ensureStorageDirectoryExists(): void
    {
        if (is_dir($this->storageDirectory)) {
            return;
        }

        if (!mkdir($this->storageDirectory, 0775, true) && !is_dir($this->storageDirectory)) {
            throw new RuntimeException('Unable to create video storage directory.');
        }
    }
}

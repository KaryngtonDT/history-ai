<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage\Audio;

use App\Application\AudioUpload\Ports\AudioStorageInterface;
use App\Domain\Source\AudioExtension;
use App\Domain\Source\SourceId;
use RuntimeException;

final class LocalAudioStorage implements AudioStorageInterface
{
    public function __construct(
        private readonly string $storageDirectory,
    ) {
    }

    public function store(SourceId $audioId, string $sourcePath, string $originalFilename): string
    {
        if (!is_file($sourcePath)) {
            throw new RuntimeException('Audio upload source file is missing.');
        }

        $extension = AudioExtension::fromFilename($originalFilename);
        $this->ensureStorageDirectoryExists();

        $destinationPath = sprintf(
            '%s/%s.%s',
            rtrim($this->storageDirectory, '/\\'),
            $audioId->value,
            $extension->value,
        );

        if (!@rename($sourcePath, $destinationPath)) {
            if (!@copy($sourcePath, $destinationPath)) {
                throw new RuntimeException('Unable to move uploaded audio into storage.');
            }

            @unlink($sourcePath);
        }

        return $destinationPath;
    }

    public function delete(string $storagePath): void
    {
        if (is_file($storagePath)) {
            @unlink($storagePath);
        }
    }

    private function ensureStorageDirectoryExists(): void
    {
        if (is_dir($this->storageDirectory)) {
            return;
        }

        if (!mkdir($this->storageDirectory, 0775, true) && !is_dir($this->storageDirectory)) {
            throw new RuntimeException('Unable to create audio source storage directory.');
        }
    }
}

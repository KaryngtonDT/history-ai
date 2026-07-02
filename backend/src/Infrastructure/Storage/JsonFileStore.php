<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use JsonException;
use RuntimeException;

final class JsonFileStore
{
    public function __construct(private readonly string $directory)
    {
        $this->ensureDirectoryExists($this->directory);
    }

    public function directory(): string
    {
        return $this->directory;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function write(string $filename, array $data): void
    {
        $path = $this->pathFor($filename);

        try {
            $encoded = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (JsonException $exception) {
            throw new RuntimeException(sprintf('Unable to encode JSON for "%s".', $filename), 0, $exception);
        }

        if (false === file_put_contents($path, $encoded, LOCK_EX)) {
            throw new RuntimeException(sprintf('Unable to write file "%s".', $path));
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function read(string $filename): ?array
    {
        $path = $this->pathFor($filename);

        if (!is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new RuntimeException(sprintf('Unable to read file "%s".', $path));
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(sprintf('Invalid JSON in file "%s".', $path), 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Expected JSON object in file "%s".', $path));
        }

        return $decoded;
    }

    /**
     * @return list<string>
     */
    public function listJsonFiles(): array
    {
        if (!is_dir($this->directory)) {
            return [];
        }

        $files = glob($this->directory . '/*.json') ?: [];

        return array_values(array_map(static fn (string $path): string => basename($path), $files));
    }

    public function delete(string $filename): void
    {
        $path = $this->pathFor($filename);

        if (is_file($path)) {
            unlink($path);
        }
    }

    public function isWritable(): bool
    {
        return is_dir($this->directory) && is_writable($this->directory);
    }

    private function pathFor(string $filename): string
    {
        return $this->directory . '/' . ltrim($filename, '/');
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Unable to create directory "%s".', $directory));
        }
    }
}

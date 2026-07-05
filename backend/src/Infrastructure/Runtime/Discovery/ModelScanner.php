<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

final class ModelScanner
{
    public function __construct(private readonly string $modelsRoot)
    {
    }

    public function directoryExists(string $relativePath): bool
    {
        $path = rtrim($this->modelsRoot, '/\\').DIRECTORY_SEPARATOR.trim($relativePath, '/\\');

        return is_dir($path);
    }

    public function resolvePath(string $relativePath): string
    {
        return rtrim($this->modelsRoot, '/\\').DIRECTORY_SEPARATOR.trim($relativePath, '/\\');
    }
}

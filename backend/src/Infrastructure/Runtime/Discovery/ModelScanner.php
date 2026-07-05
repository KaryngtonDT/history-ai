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
        $path = $this->resolvePath($relativePath);

        return is_dir($path);
    }

    public function hasUsableContent(string $relativePath): bool
    {
        $path = $this->resolvePath($relativePath);

        if (!is_dir($path)) {
            return false;
        }

        return $this->directoryHasFiles($path);
    }

    public function resolvePath(string $relativePath): string
    {
        return rtrim($this->modelsRoot, '/\\').DIRECTORY_SEPARATOR.trim($relativePath, '/\\');
    }

    private function directoryHasFiles(string $path): bool
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getSize() > 0) {
                return true;
            }
        }

        return false;
    }
}

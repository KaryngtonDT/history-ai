<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

final class PythonScanner
{
    public function __construct(private readonly BinaryScanner $binaryScanner)
    {
    }

    public function version(): ?string
    {
        $path = $this->binaryScanner->locate('python3') ?? $this->binaryScanner->locate('python');

        if (null === $path) {
            return null;
        }

        $output = shell_exec(escapeshellarg($path).' --version 2>&1');

        return is_string($output) ? trim($output) : null;
    }

    public function isAvailable(): bool
    {
        return null !== $this->version();
    }
}

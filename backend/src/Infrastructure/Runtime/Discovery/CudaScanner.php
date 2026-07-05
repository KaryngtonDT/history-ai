<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

final class CudaScanner
{
    public function __construct(private readonly BinaryScanner $binaryScanner)
    {
    }

    public function isAvailable(): bool
    {
        if (!$this->binaryScanner->exists('nvidia-smi')) {
            return false;
        }

        $output = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');

        return is_string($output) && '' !== trim($output);
    }

    public function deviceName(): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $output = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');

        return is_string($output) ? trim(explode("\n", trim($output))[0]) : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class CudaDetector
{
    public function isAvailable(): bool
    {
        if ($this->binaryExists('nvidia-smi')) {
            $output = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');

            return is_string($output) && '' !== trim($output);
        }

        $output = shell_exec('python3 -c "import torch; print(torch.cuda.is_available())" 2>/dev/null');

        return is_string($output) && str_contains(strtolower(trim($output)), 'true');
    }

    private function binaryExists(string $binary): bool
    {
        $output = shell_exec('command -v '.escapeshellarg($binary).' 2>/dev/null');

        return is_string($output) && '' !== trim($output);
    }
}

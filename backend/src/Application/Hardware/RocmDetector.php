<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class RocmDetector
{
    public function isAvailable(): bool
    {
        if ($this->binaryExists('rocm-smi')) {
            return true;
        }

        $output = shell_exec('python3 -c "import torch; print(getattr(torch.version, \'hip\', None) is not None)" 2>/dev/null');

        return is_string($output) && str_contains(strtolower(trim($output)), 'true');
    }

    private function binaryExists(string $binary): bool
    {
        $output = shell_exec('command -v '.escapeshellarg($binary).' 2>/dev/null');

        return is_string($output) && '' !== trim($output);
    }
}

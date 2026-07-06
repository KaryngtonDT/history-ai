<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class DockerResourceDetector
{
    /**
     * @return array{gpuAccess: bool, memoryLimitGb: ?float}
     */
    public function detect(): array
    {
        $insideDocker = file_exists('/.dockerenv') || (bool) getenv('DOCKER_CONTAINER');
        $gpuAccess = false;

        if ($this->binaryExists('nvidia-smi')) {
            $output = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');
            $gpuAccess = is_string($output) && '' !== trim($output);
        }

        $memoryLimitGb = null;
        if ($insideDocker && is_readable('/sys/fs/cgroup/memory.max')) {
            $raw = trim((string) file_get_contents('/sys/fs/cgroup/memory.max'));
            if (is_numeric($raw) && (int) $raw > 0) {
                $memoryLimitGb = round(((int) $raw) / 1024 / 1024 / 1024, 1);
            }
        }

        return ['gpuAccess' => $gpuAccess, 'memoryLimitGb' => $memoryLimitGb];
    }

    private function binaryExists(string $binary): bool
    {
        $output = shell_exec('command -v '.escapeshellarg($binary).' 2>/dev/null');

        return is_string($output) && '' !== trim($output);
    }
}

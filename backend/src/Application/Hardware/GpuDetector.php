<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class GpuDetector
{
    /**
     * @return array{vendor: ?string, name: ?string, vramGb: ?float}
     */
    public function detect(): array
    {
        $nvidiaName = $this->nvidiaGpuName();
        if (null !== $nvidiaName) {
            return [
                'vendor' => 'NVIDIA',
                'name' => $nvidiaName,
                'vramGb' => $this->nvidiaVramGb(),
            ];
        }

        $lspci = shell_exec('lspci 2>/dev/null');
        if (is_string($lspci) && '' !== trim($lspci)) {
            foreach (explode("\n", $lspci) as $line) {
                if (!preg_match('/VGA|3D|Display/i', $line)) {
                    continue;
                }

                $name = trim(preg_replace('/^[^:]+:\s*/', '', $line) ?? $line);
                $vendor = match (true) {
                    str_contains(strtolower($name), 'nvidia') => 'NVIDIA',
                    str_contains(strtolower($name), 'amd') || str_contains(strtolower($name), 'radeon') => 'AMD',
                    str_contains(strtolower($name), 'intel') => 'Intel',
                    default => 'Unknown',
                };

                return ['vendor' => $vendor, 'name' => $name, 'vramGb' => null];
            }
        }

        $wmic = shell_exec('wmic path win32_VideoController get name 2>nul');
        if (is_string($wmic) && str_contains($wmic, 'Name')) {
            foreach (explode("\n", $wmic) as $line) {
                $line = trim($line);
                if ('' === $line || 'Name' === $line) {
                    continue;
                }

                $lower = strtolower($line);
                $vendor = match (true) {
                    str_contains($lower, 'nvidia') => 'NVIDIA',
                    str_contains($lower, 'amd') || str_contains($lower, 'radeon') => 'AMD',
                    str_contains($lower, 'intel') => 'Intel',
                    default => 'Unknown',
                };

                return ['vendor' => $vendor, 'name' => $line, 'vramGb' => null];
            }
        }

        return ['vendor' => null, 'name' => null, 'vramGb' => null];
    }

    private function nvidiaGpuName(): ?string
    {
        $output = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');

        return is_string($output) && '' !== trim($output) ? trim(explode("\n", trim($output))[0]) : null;
    }

    private function nvidiaVramGb(): ?float
    {
        $output = shell_exec('nvidia-smi --query-gpu=memory.total --format=csv,noheader,nounits 2>/dev/null');
        if (!is_string($output) || '' === trim($output)) {
            return null;
        }

        $mib = (float) trim(explode("\n", trim($output))[0]);

        return round($mib / 1024, 1);
    }
}

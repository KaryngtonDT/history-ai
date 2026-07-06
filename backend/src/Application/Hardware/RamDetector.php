<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class RamDetector
{
    /**
     * @return array{totalGb: ?float, availableGb: ?float, cpuModel: ?string}
     */
    public function detect(): array
    {
        $meminfo = @file_get_contents('/proc/meminfo');
        if (is_string($meminfo) && '' !== $meminfo) {
            $totalKb = $this->readMemValue($meminfo, 'MemTotal');
            $availableKb = $this->readMemValue($meminfo, 'MemAvailable') ?? $this->readMemValue($meminfo, 'MemFree');
            $cpuModel = $this->readCpuModelLinux();

            return [
                'totalGb' => null !== $totalKb ? round($totalKb / 1024 / 1024, 1) : null,
                'availableGb' => null !== $availableKb ? round($availableKb / 1024 / 1024, 1) : null,
                'cpuModel' => $cpuModel,
            ];
        }

        return [
            'totalGb' => $this->windowsTotalGb(),
            'availableGb' => $this->windowsAvailableGb(),
            'cpuModel' => $this->windowsCpuModel(),
        ];
    }

    private function readMemValue(string $meminfo, string $key): ?int
    {
        if (preg_match('/^'.$key.':\s+(\d+)/m', $meminfo, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function readCpuModelLinux(): ?string
    {
        $cpuinfo = @file_get_contents('/proc/cpuinfo');
        if (!is_string($cpuinfo)) {
            return null;
        }

        if (preg_match('/model name\s*:\s*(.+)/i', $cpuinfo, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function windowsTotalGb(): ?float
    {
        $output = shell_exec('wmic computersystem get TotalPhysicalMemory /value 2>nul');
        if (!is_string($output) || !preg_match('/TotalPhysicalMemory=(\d+)/', $output, $matches)) {
            return null;
        }

        return round(((float) $matches[1]) / 1024 / 1024 / 1024, 1);
    }

    private function windowsAvailableGb(): ?float
    {
        $output = shell_exec('wmic OS get FreePhysicalMemory /value 2>nul');
        if (!is_string($output) || !preg_match('/FreePhysicalMemory=(\d+)/', $output, $matches)) {
            return null;
        }

        return round(((float) $matches[1]) / 1024 / 1024, 1);
    }

    private function windowsCpuModel(): ?string
    {
        $output = shell_exec('wmic cpu get Name /value 2>nul');
        if (!is_string($output) || !preg_match('/Name=(.+)/', $output, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }
}

<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class DirectMlDetector
{
    public function isAvailable(): bool
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return false;
        }

        $output = shell_exec('powershell -NoProfile -Command "(Get-WmiObject Win32_VideoController).Name" 2>nul');
        if (!is_string($output)) {
            return false;
        }

        return str_contains(strtolower($output), 'amd')
            || str_contains(strtolower($output), 'intel')
            || str_contains(strtolower($output), 'radeon');
    }
}

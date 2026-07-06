<?php

declare(strict_types=1);

namespace App\Application\Hardware;

final class WslDetector
{
    public function isWsl2(): bool
    {
        if (is_readable('/proc/version')) {
            $version = strtolower((string) file_get_contents('/proc/version'));

            return str_contains($version, 'microsoft') || str_contains($version, 'wsl');
        }

        $output = shell_exec('wsl.exe --status 2>nul');

        return is_string($output) && '' !== trim($output);
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Discovery;

final class BinaryScanner
{
    public function locate(string $binary): ?string
    {
        $escaped = escapeshellarg($binary);
        $output = shell_exec('command -v '.$escaped.' 2>/dev/null');

        if (!is_string($output) || '' === trim($output)) {
            return null;
        }

        return trim($output);
    }

    public function exists(string $binary): bool
    {
        return null !== $this->locate($binary);
    }
}

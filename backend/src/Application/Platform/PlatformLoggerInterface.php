<?php

declare(strict_types=1);

namespace App\Application\Platform;

interface PlatformLoggerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function info(string $component, string $message, array $context = []): void;
}

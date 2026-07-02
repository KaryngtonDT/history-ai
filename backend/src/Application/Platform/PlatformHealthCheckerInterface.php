<?php

declare(strict_types=1);

namespace App\Application\Platform;

interface PlatformHealthCheckerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function liveness(): array;

    /**
     * @return array<string, mixed>
     */
    public function readiness(): array;

    /**
     * @return array<string, mixed>
     */
    public function live(): array;

    /**
     * @return array<string, mixed>
     */
    public function productionReadiness(): array;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

interface LatentSyncProcessRunner
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string;
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

interface F5ProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string;
}

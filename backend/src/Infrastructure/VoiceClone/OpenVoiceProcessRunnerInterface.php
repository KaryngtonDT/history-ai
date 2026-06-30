<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

interface OpenVoiceProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string;
}

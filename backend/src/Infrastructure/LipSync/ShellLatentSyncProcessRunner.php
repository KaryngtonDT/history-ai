<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

use App\Infrastructure\LipSync\Exception\LatentSyncProviderException;
use Symfony\Component\Process\Process;

final class ShellLatentSyncProcessRunner implements LatentSyncProcessRunner
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        $process = new Process($command);
        $process->setTimeout(1200);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new LatentSyncProviderException(trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        return trim($process->getOutput());
    }
}

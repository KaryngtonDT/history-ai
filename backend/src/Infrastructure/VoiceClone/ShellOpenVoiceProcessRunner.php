<?php

declare(strict_types=1);

namespace App\Infrastructure\VoiceClone;

use App\Infrastructure\VoiceClone\Exception\OpenVoiceProviderException;
use Symfony\Component\Process\Process;

final class ShellOpenVoiceProcessRunner implements OpenVoiceProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new OpenVoiceProviderException(trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        return trim($process->getOutput());
    }
}

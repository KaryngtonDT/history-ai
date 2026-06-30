<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

use App\Infrastructure\VideoRender\Exception\FFmpegProviderException;
use Symfony\Component\Process\Process;

final class ShellFFmpegProcessRunner implements FFmpegProcessRunner
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        $process = new Process($command);
        $process->setTimeout(1800);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new FFmpegProviderException(trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        return trim($process->getOutput());
    }
}

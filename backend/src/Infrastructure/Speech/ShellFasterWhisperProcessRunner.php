<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final class ShellFasterWhisperProcessRunner implements FasterWhisperProcessRunnerInterface
{
    public function __construct(
        private readonly ?float $timeoutSeconds = null,
    ) {
    }

    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        if ([] === $command) {
            throw new FasterWhisperProviderException('Speech process command cannot be empty.');
        }

        $process = new Process($command);
        if (null !== $this->timeoutSeconds && $this->timeoutSeconds > 0) {
            $process->setTimeout($this->timeoutSeconds);
        }

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            throw new FasterWhisperProviderException(
                sprintf('Speech process timed out after %.0f seconds.', $this->timeoutSeconds ?? 0),
            );
        }

        $stdout = trim($process->getOutput());
        if (!$process->isSuccessful()) {
            $stderr = trim($process->getErrorOutput());
            $detail = '' !== $stderr ? $stderr : ('exit code '.$process->getExitCode());

            throw new FasterWhisperProviderException('Speech process failed: '.$detail);
        }

        if ('' === $stdout) {
            throw new FasterWhisperProviderException('Speech process returned no output.');
        }

        return $stdout;
    }
}

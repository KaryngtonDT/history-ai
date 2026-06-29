<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

use App\Infrastructure\Speech\Exception\FasterWhisperProviderException;

final class ShellFasterWhisperProcessRunner implements FasterWhisperProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        if ([] === $command) {
            throw new FasterWhisperProviderException('Speech process command cannot be empty.');
        }

        $escaped = array_map(static fn (string $part): string => escapeshellarg($part), $command);
        $line = implode(' ', $escaped);
        $output = shell_exec($line.' 2>&1');

        if (!is_string($output) || '' === trim($output)) {
            throw new FasterWhisperProviderException('Speech process returned no output.');
        }

        return $output;
    }
}

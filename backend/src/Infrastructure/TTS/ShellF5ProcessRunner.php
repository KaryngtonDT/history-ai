<?php

declare(strict_types=1);

namespace App\Infrastructure\TTS;

use App\Infrastructure\TTS\Exception\F5TextToSpeechProviderException;

final class ShellF5ProcessRunner implements F5ProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        if ([] === $command) {
            throw new F5TextToSpeechProviderException('F5 process command cannot be empty.');
        }

        $escaped = array_map(static fn (string $part): string => escapeshellarg($part), $command);
        $line = implode(' ', $escaped);
        $output = shell_exec($line.' 2>&1');

        if (!is_string($output) || '' === trim($output)) {
            throw new F5TextToSpeechProviderException('F5 process returned no output.');
        }

        return $output;
    }
}

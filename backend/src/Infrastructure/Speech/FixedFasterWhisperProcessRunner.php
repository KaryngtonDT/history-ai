<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

final class FixedFasterWhisperProcessRunner implements FasterWhisperProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string
    {
        $storagePath = $command[1] ?? '';

        return json_encode([
            'language' => 'en',
            'segments' => [
                [
                    'index' => 0,
                    'start' => 0.0,
                    'end' => 2.5,
                    'text' => sprintf('Deterministic transcript for %s', basename($storagePath)),
                ],
                [
                    'index' => 1,
                    'start' => 2.5,
                    'end' => 5.0,
                    'text' => 'Second deterministic segment.',
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }
}

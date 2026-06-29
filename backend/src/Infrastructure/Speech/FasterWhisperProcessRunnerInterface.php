<?php

declare(strict_types=1);

namespace App\Infrastructure\Speech;

interface FasterWhisperProcessRunnerInterface
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string;
}

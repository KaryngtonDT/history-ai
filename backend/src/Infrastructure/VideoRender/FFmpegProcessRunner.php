<?php

declare(strict_types=1);

namespace App\Infrastructure\VideoRender;

interface FFmpegProcessRunner
{
    /**
     * @param list<string> $command
     */
    public function run(array $command): string;
}

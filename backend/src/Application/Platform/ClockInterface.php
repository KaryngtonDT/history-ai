<?php

declare(strict_types=1);

namespace App\Application\Platform;

interface ClockInterface
{
    public function now(): float;
}

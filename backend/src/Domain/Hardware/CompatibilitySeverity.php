<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

enum CompatibilitySeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Blocking = 'blocking';
}

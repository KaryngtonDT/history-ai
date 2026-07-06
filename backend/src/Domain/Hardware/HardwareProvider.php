<?php

declare(strict_types=1);

namespace App\Domain\Hardware;

enum HardwareProvider: string
{
    case Host = 'host';
    case Docker = 'docker';
    case Remote = 'remote';

    public function label(): string
    {
        return match ($this) {
            self::Host => 'Host',
            self::Docker => 'Docker',
            self::Remote => 'Remote',
        };
    }
}

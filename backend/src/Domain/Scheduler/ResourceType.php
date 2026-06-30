<?php

declare(strict_types=1);

namespace App\Domain\Scheduler;

enum ResourceType: string
{
    case Cpu = 'cpu';
    case Gpu = 'gpu';
    case Io = 'io';

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}

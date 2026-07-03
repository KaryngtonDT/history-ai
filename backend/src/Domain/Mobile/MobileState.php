<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

enum MobileState: string
{
    case Connected = 'connected';
    case Idle = 'idle';
    case Disconnected = 'disconnected';
}

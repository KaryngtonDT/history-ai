<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

enum PresenceState: string
{
    case Connected = 'connected';
    case Idle = 'idle';
    case Disconnected = 'disconnected';
}

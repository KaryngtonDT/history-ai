<?php

declare(strict_types=1);

namespace App\Domain\ShadowBrowser;

enum BrowserState: string
{
    case Connected = 'connected';
    case Idle = 'idle';
    case Disconnected = 'disconnected';
}

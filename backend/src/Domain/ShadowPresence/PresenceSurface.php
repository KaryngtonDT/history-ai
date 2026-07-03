<?php

declare(strict_types=1);

namespace App\Domain\ShadowPresence;

enum PresenceSurface: string
{
    case Web = 'web';
    case Desktop = 'desktop';
    case Browser = 'browser';
    case Ide = 'ide';
    case Mobile = 'mobile';
}

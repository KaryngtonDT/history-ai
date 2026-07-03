<?php

declare(strict_types=1);

namespace App\Domain\Mobile;

enum MobileConnectionMode: string
{
    case Localhost = 'localhost';
    case Lan = 'lan';
    case Tailscale = 'tailscale';
    case Auto = 'auto';
    case Cloud = 'cloud';
}

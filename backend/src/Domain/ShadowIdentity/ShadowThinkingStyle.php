<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowThinkingStyle: string
{
    case Analytical = 'analytical';
    case Intuitive = 'intuitive';
    case Socratic = 'socratic';
    case Exploratory = 'exploratory';
    case Structured = 'structured';
}

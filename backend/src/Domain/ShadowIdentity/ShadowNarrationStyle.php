<?php

declare(strict_types=1);

namespace App\Domain\ShadowIdentity;

enum ShadowNarrationStyle: string
{
    case Neutral = 'neutral';
    case Storytelling = 'storytelling';
    case Documentary = 'documentary';
    case Professor = 'professor';
    case Coach = 'coach';
    case Friendly = 'friendly';
    case Debate = 'debate';
    case Socratic = 'socratic';
}

<?php

declare(strict_types=1);

namespace App\Domain\Shadow\SessionLearning;

enum SessionVoiceStyleKind: string
{
    case Calm = 'calm';
    case Dynamic = 'dynamic';
    case Neutral = 'neutral';
    case Storyteller = 'storyteller';
}

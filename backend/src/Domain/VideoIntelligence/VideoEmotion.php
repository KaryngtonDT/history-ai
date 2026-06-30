<?php

declare(strict_types=1);

namespace App\Domain\VideoIntelligence;

enum VideoEmotion: string
{
    case Neutral = 'neutral';
    case Happy = 'happy';
    case Sad = 'sad';
    case Angry = 'angry';
    case Excited = 'excited';
}

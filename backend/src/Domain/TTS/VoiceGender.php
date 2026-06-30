<?php

declare(strict_types=1);

namespace App\Domain\TTS;

enum VoiceGender: string
{
    case Male = 'male';
    case Female = 'female';
    case Neutral = 'neutral';
}

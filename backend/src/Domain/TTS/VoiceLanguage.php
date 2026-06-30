<?php

declare(strict_types=1);

namespace App\Domain\TTS;

enum VoiceLanguage: string
{
    case English = 'english';
    case French = 'french';
    case German = 'german';
    case Spanish = 'spanish';
    case Italian = 'italian';
}

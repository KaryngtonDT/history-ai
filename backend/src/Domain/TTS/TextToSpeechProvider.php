<?php

declare(strict_types=1);

namespace App\Domain\TTS;

enum TextToSpeechProvider: string
{
    case F5TTS = 'f5_tts';
    case Kokoro = 'kokoro';
    case XTTS = 'xtts';
    case Mock = 'mock';
}

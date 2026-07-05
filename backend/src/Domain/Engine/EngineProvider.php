<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum EngineProvider: string
{
    case FasterWhisper = 'faster_whisper';
    case Ollama = 'ollama';
    case F5Tts = 'f5_tts';
    case Kokoro = 'kokoro';
    case OpenVoice = 'openvoice';
    case LatentSync = 'latentsync';
    case Ffmpeg = 'ffmpeg';
}
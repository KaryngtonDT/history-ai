<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum EngineFamily: string
{
    case Whisper = 'whisper';
    case Ollama = 'ollama';
    case Tts = 'tts';
    case VoiceClone = 'voice_clone';
    case LipSync = 'lip_sync';
    case Ffmpeg = 'ffmpeg';
}

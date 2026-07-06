<?php

declare(strict_types=1);

namespace App\Domain\Engine;

enum EngineFamily: string
{
    case Whisper = 'whisper';
    case WhisperCpp = 'whisper_cpp';
    case Ollama = 'ollama';
    case Tts = 'tts';
    case Piper = 'piper';
    case VoiceClone = 'voice_clone';
    case LipSync = 'lip_sync';
    case LivePortrait = 'live_portrait';
    case Ffmpeg = 'ffmpeg';
    case Ocr = 'ocr';
    case Vision = 'vision';
    case Embeddings = 'embeddings';
    case Reranker = 'reranker';
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\AI\AIEngine;
use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineConfiguration;
use App\Domain\AI\AIEngineId;
use App\Domain\AI\AIEngineProvider;
use App\Domain\AI\AIEngineRegistry;

final class AIEngineRegistryFactory
{
    public const string PROVIDER_FASTER_WHISPER = 'faster_whisper';
    public const string PROVIDER_OLLAMA = 'ollama';
    public const string PROVIDER_F5_TTS = 'f5_tts';
    public const string PROVIDER_KOKORO = 'kokoro';
    public const string PROVIDER_XTTS = 'xtts';
    public const string PROVIDER_OPENVOICE = 'openvoice';
    public const string PROVIDER_SEEDVC = 'seedvc';
    public const string PROVIDER_LATENTSYNC = 'latentsync';
    public const string PROVIDER_WAV2LIP = 'wav2lip';
    public const string PROVIDER_FFMPEG = 'ffmpeg';

    public function create(): AIEngineRegistry
    {
        return AIEngineRegistry::fromEngines([
            AIEngine::create(
                new AIEngineId('speech-to-text'),
                AIEngineCapability::SpeechToText,
                [
                    AIEngineProvider::create(
                        self::PROVIDER_FASTER_WHISPER,
                        'Faster Whisper',
                        AIEngineCapability::SpeechToText,
                    ),
                ],
            ),
            AIEngine::create(
                new AIEngineId('translation'),
                AIEngineCapability::Translation,
                [
                    AIEngineProvider::create(
                        self::PROVIDER_OLLAMA,
                        'Ollama',
                        AIEngineCapability::Translation,
                    ),
                ],
            ),
            AIEngine::create(
                new AIEngineId('text-to-speech'),
                AIEngineCapability::TextToSpeech,
                [
                    AIEngineProvider::create('f5_tts', 'F5-TTS', AIEngineCapability::TextToSpeech),
                    AIEngineProvider::create('kokoro', 'Kokoro', AIEngineCapability::TextToSpeech, false),
                    AIEngineProvider::create('xtts', 'XTTS', AIEngineCapability::TextToSpeech, false),
                ],
            ),
            AIEngine::create(
                new AIEngineId('voice-clone'),
                AIEngineCapability::VoiceClone,
                [
                    AIEngineProvider::create('openvoice', 'OpenVoice V2', AIEngineCapability::VoiceClone),
                    AIEngineProvider::create('seedvc', 'SeedVC', AIEngineCapability::VoiceClone, false),
                ],
            ),
            AIEngine::create(
                new AIEngineId('lip-sync'),
                AIEngineCapability::LipSync,
                [
                    AIEngineProvider::create('latentsync', 'LatentSync', AIEngineCapability::LipSync),
                    AIEngineProvider::create('wav2lip', 'Wav2Lip', AIEngineCapability::LipSync, false),
                ],
            ),
            AIEngine::create(
                new AIEngineId('video-render'),
                AIEngineCapability::VideoRender,
                [
                    AIEngineProvider::create(self::PROVIDER_FFMPEG, 'FFmpeg', AIEngineCapability::VideoRender),
                ],
            ),
        ]);
    }

    public function createConfiguration(): AIEngineConfiguration
    {
        return AIEngineConfiguration::empty()
            ->withDefaultProvider(AIEngineCapability::SpeechToText, self::PROVIDER_FASTER_WHISPER)
            ->withDefaultProvider(AIEngineCapability::Translation, self::PROVIDER_OLLAMA)
            ->withDefaultProvider(AIEngineCapability::TextToSpeech, self::PROVIDER_F5_TTS)
            ->withDefaultProvider(AIEngineCapability::VoiceClone, self::PROVIDER_OPENVOICE)
            ->withDefaultProvider(AIEngineCapability::LipSync, self::PROVIDER_LATENTSYNC)
            ->withDefaultProvider(AIEngineCapability::VideoRender, self::PROVIDER_FFMPEG);
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineFamily;

final readonly class EngineDefinition
{
    public function __construct(
        public string $id,
        public string $displayName,
        public EngineCatalogCapability $capability,
        public EngineFamily $family,
        public ?string $binaryName = null,
        public ?string $modelPath = null,
        public ?string $documentationUrl = null,
        public bool $default = false,
    ) {
    }
}

final class EngineCatalogDefinitions
{
    /**
     * @return list<EngineDefinition>
     */
    public static function all(): array
    {
        return [
            new EngineDefinition('faster_whisper', 'Faster Whisper', EngineCatalogCapability::SpeechToText, EngineFamily::Whisper, 'faster-whisper', 'whisper', default: true),
            new EngineDefinition('parakeet', 'NVIDIA Parakeet', EngineCatalogCapability::SpeechToText, EngineFamily::Whisper, 'parakeet'),
            new EngineDefinition('canary', 'NVIDIA Canary', EngineCatalogCapability::SpeechToText, EngineFamily::Whisper, 'canary'),
            new EngineDefinition('ollama', 'Ollama', EngineCatalogCapability::Translation, EngineFamily::Ollama, default: true),
            new EngineDefinition('f5_tts', 'F5-TTS', EngineCatalogCapability::TextToSpeech, EngineFamily::Tts, 'f5-tts', 'f5', default: true),
            new EngineDefinition('kokoro', 'Kokoro TTS', EngineCatalogCapability::TextToSpeech, EngineFamily::Tts, 'kokoro', 'kokoro'),
            new EngineDefinition('openvoice', 'OpenVoice V2', EngineCatalogCapability::VoiceClone, EngineFamily::VoiceClone, 'openvoice', 'openvoice', default: true),
            new EngineDefinition('latentsync', 'LatentSync', EngineCatalogCapability::LipSync, EngineFamily::LipSync, 'latentsync', 'latentsync', default: true),
            new EngineDefinition('ffmpeg', 'FFmpeg', EngineCatalogCapability::VideoRender, EngineFamily::Ffmpeg, 'ffmpeg', default: true),
        ];
    }
}

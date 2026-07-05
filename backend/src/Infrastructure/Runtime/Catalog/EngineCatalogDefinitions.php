<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineCatalogRole;
use App\Domain\Engine\EngineFamily;
use App\Domain\Runtime\EngineExecutionMode;

final readonly class EngineDefinition
{
    public function __construct(
        public string $id,
        public string $displayName,
        public EngineCatalogCapability $capability,
        public EngineFamily $family,
        public EngineCatalogRole $role,
        public ?string $binaryName = null,
        public ?string $modelPath = null,
        public ?string $expectedModel = null,
        public ?string $ollamaModelTag = null,
        public bool $requiresModelFiles = true,
        public bool $usesHuggingFaceCache = false,
        public EngineExecutionMode $installedMode = EngineExecutionMode::Real,
        public ?string $documentationUrl = null,
    ) {
    }

    public function isDefault(): bool
    {
        return EngineCatalogRole::Default === $this->role;
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
            // Speech-to-Text
            new EngineDefinition(
                'faster_whisper_large_v3',
                'Faster Whisper Large V3',
                EngineCatalogCapability::SpeechToText,
                EngineFamily::Whisper,
                EngineCatalogRole::Default,
                'faster-whisper',
                null,
                'large-v3',
                usesHuggingFaceCache: true,
            ),
            new EngineDefinition(
                'parakeet',
                'NVIDIA Parakeet',
                EngineCatalogCapability::SpeechToText,
                EngineFamily::Whisper,
                EngineCatalogRole::Alternative1,
                'parakeet',
                'parakeet',
            ),
            new EngineDefinition(
                'canary',
                'NVIDIA Canary',
                EngineCatalogCapability::SpeechToText,
                EngineFamily::Whisper,
                EngineCatalogRole::Alternative2,
                'canary',
                'canary',
            ),

            // Translation (Ollama + model variants)
            new EngineDefinition(
                'ollama_gemma3',
                'Ollama + Gemma 3',
                EngineCatalogCapability::Translation,
                EngineFamily::Ollama,
                EngineCatalogRole::Default,
                ollamaModelTag: 'gemma3',
                requiresModelFiles: false,
            ),
            new EngineDefinition(
                'ollama_qwen3',
                'Ollama + Qwen 3',
                EngineCatalogCapability::Translation,
                EngineFamily::Ollama,
                EngineCatalogRole::Alternative1,
                ollamaModelTag: 'qwen3',
                requiresModelFiles: false,
            ),
            new EngineDefinition(
                'ollama_deepseek_r1_distill',
                'Ollama + DeepSeek R1 Distill',
                EngineCatalogCapability::Translation,
                EngineFamily::Ollama,
                EngineCatalogRole::Alternative2,
                ollamaModelTag: 'deepseek-r1',
                requiresModelFiles: false,
            ),

            // Text-to-Speech
            new EngineDefinition(
                'f5_tts',
                'F5-TTS',
                EngineCatalogCapability::TextToSpeech,
                EngineFamily::Tts,
                EngineCatalogRole::Default,
                'f5-tts',
                'f5',
                'F5-TTS',
                installedMode: EngineExecutionMode::Shim,
            ),
            new EngineDefinition(
                'kokoro',
                'Kokoro TTS',
                EngineCatalogCapability::TextToSpeech,
                EngineFamily::Tts,
                EngineCatalogRole::Alternative1,
                'kokoro',
                'kokoro',
            ),
            new EngineDefinition(
                'dia',
                'Dia',
                EngineCatalogCapability::TextToSpeech,
                EngineFamily::Tts,
                EngineCatalogRole::Alternative2,
                'dia',
                'dia',
            ),

            // Voice Clone
            new EngineDefinition(
                'openvoice_v2',
                'OpenVoice V2',
                EngineCatalogCapability::VoiceClone,
                EngineFamily::VoiceClone,
                EngineCatalogRole::Default,
                'openvoice',
                'openvoice',
                'openvoice_v2',
                installedMode: EngineExecutionMode::Shim,
            ),
            new EngineDefinition(
                'chatterbox',
                'Chatterbox',
                EngineCatalogCapability::VoiceClone,
                EngineFamily::VoiceClone,
                EngineCatalogRole::Alternative1,
                'chatterbox',
                'chatterbox',
            ),
            new EngineDefinition(
                'xtts_v2',
                'XTTS-v2',
                EngineCatalogCapability::VoiceClone,
                EngineFamily::VoiceClone,
                EngineCatalogRole::Alternative2,
                'xtts',
                'xtts',
            ),

            // Lip Sync
            new EngineDefinition(
                'latentsync',
                'LatentSync',
                EngineCatalogCapability::LipSync,
                EngineFamily::LipSync,
                EngineCatalogRole::Default,
                'latentsync',
                'latentsync',
                'latentsync',
                installedMode: EngineExecutionMode::Shim,
            ),
            new EngineDefinition(
                'echomimic_v2',
                'EchoMimic V2',
                EngineCatalogCapability::LipSync,
                EngineFamily::LipSync,
                EngineCatalogRole::Alternative1,
                'echomimic',
                'echomimic',
            ),
            new EngineDefinition(
                'musetalk',
                'MuseTalk',
                EngineCatalogCapability::LipSync,
                EngineFamily::LipSync,
                EngineCatalogRole::Alternative2,
                'musetalk',
                'musetalk',
            ),

            // Video Render
            new EngineDefinition(
                'ffmpeg',
                'FFmpeg',
                EngineCatalogCapability::VideoRender,
                EngineFamily::Ffmpeg,
                EngineCatalogRole::Default,
                'ffmpeg',
                requiresModelFiles: false,
            ),
            new EngineDefinition(
                'ffmpeg_nvenc',
                'FFmpeg NVENC',
                EngineCatalogCapability::VideoRender,
                EngineFamily::Ffmpeg,
                EngineCatalogRole::Alternative1,
                'ffmpeg',
                requiresModelFiles: false,
                expectedModel: 'h264_nvenc',
            ),
            new EngineDefinition(
                'ffmpeg_av1',
                'FFmpeg AV1',
                EngineCatalogCapability::VideoRender,
                EngineFamily::Ffmpeg,
                EngineCatalogRole::Alternative2,
                'ffmpeg',
                requiresModelFiles: false,
                expectedModel: 'libaom-av1',
            ),
        ];
    }

    public static function findById(string $id): ?EngineDefinition
    {
        foreach (self::all() as $definition) {
            if ($definition->id === $id) {
                return $definition;
            }
        }

        return null;
    }

    public static function defaultForCapability(EngineCatalogCapability $capability): ?EngineDefinition
    {
        foreach (self::all() as $definition) {
            if ($definition->capability === $capability && $definition->isDefault()) {
                return $definition;
            }
        }

        return null;
    }
}

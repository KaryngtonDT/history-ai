<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Engine\EngineCatalogRole;
use App\Domain\Engine\EngineCatalogTier;
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
        public EngineCatalogTier $tier = EngineCatalogTier::Default,
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
                id: 'faster_whisper_large_v3',
                displayName: 'Faster Whisper Large V3',
                capability: EngineCatalogCapability::SpeechToText,
                family: EngineFamily::Whisper,
                role: EngineCatalogRole::Default,
                tier: EngineCatalogTier::Default,
                binaryName: 'faster-whisper',
                expectedModel: 'large-v3',
                usesHuggingFaceCache: true,
            ),
            new EngineDefinition(
                id: 'whisper_cpp',
                displayName: 'Whisper.cpp',
                capability: EngineCatalogCapability::SpeechToText,
                family: EngineFamily::WhisperCpp,
                role: EngineCatalogRole::Alternative1,
                tier: EngineCatalogTier::CpuAlternative,
                binaryName: 'whisper-cpp',
                modelPath: 'whisper-cpp',
            ),
            new EngineDefinition(
                id: 'parakeet',
                displayName: 'NVIDIA Parakeet',
                capability: EngineCatalogCapability::SpeechToText,
                family: EngineFamily::Whisper,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::PremiumNvidia,
                binaryName: 'parakeet',
                modelPath: 'parakeet',
            ),
            new EngineDefinition(
                id: 'canary',
                displayName: 'NVIDIA Canary',
                capability: EngineCatalogCapability::SpeechToText,
                family: EngineFamily::Whisper,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Alternative,
                binaryName: 'canary',
                modelPath: 'canary',
            ),

            // Translation (Ollama + model variants)
            new EngineDefinition(
                id: 'ollama_gemma3',
                displayName: 'Ollama + Gemma 3',
                capability: EngineCatalogCapability::Translation,
                family: EngineFamily::Ollama,
                role: EngineCatalogRole::Default,
                ollamaModelTag: 'gemma3',
                requiresModelFiles: false,
            ),
            new EngineDefinition(
                id: 'ollama_qwen3',
                displayName: 'Ollama + Qwen 3',
                capability: EngineCatalogCapability::Translation,
                family: EngineFamily::Ollama,
                role: EngineCatalogRole::Alternative1,
                ollamaModelTag: 'qwen3',
                requiresModelFiles: false,
            ),
            new EngineDefinition(
                id: 'ollama_deepseek_r1_distill',
                displayName: 'Ollama + DeepSeek R1 Distill',
                capability: EngineCatalogCapability::Translation,
                family: EngineFamily::Ollama,
                role: EngineCatalogRole::Alternative2,
                ollamaModelTag: 'deepseek-r1',
                requiresModelFiles: false,
            ),

            // Text-to-Speech
            new EngineDefinition(
                id: 'f5_tts',
                displayName: 'F5-TTS',
                capability: EngineCatalogCapability::TextToSpeech,
                family: EngineFamily::Tts,
                role: EngineCatalogRole::Default,
                binaryName: 'f5-tts',
                modelPath: 'f5',
                expectedModel: 'F5-TTS',
                installedMode: EngineExecutionMode::Real,
            ),
            new EngineDefinition(
                id: 'kokoro',
                displayName: 'Kokoro TTS',
                capability: EngineCatalogCapability::TextToSpeech,
                family: EngineFamily::Tts,
                role: EngineCatalogRole::Alternative1,
                tier: EngineCatalogTier::CpuAlternative,
                binaryName: 'kokoro',
                modelPath: 'kokoro',
            ),
            new EngineDefinition(
                id: 'piper',
                displayName: 'Piper TTS',
                capability: EngineCatalogCapability::TextToSpeech,
                family: EngineFamily::Piper,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Lightweight,
                binaryName: 'piper',
                modelPath: 'piper',
            ),
            new EngineDefinition(
                id: 'dia',
                displayName: 'Dia',
                capability: EngineCatalogCapability::TextToSpeech,
                family: EngineFamily::Tts,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Experimental,
                binaryName: 'dia',
                modelPath: 'dia',
            ),

            // Voice Clone
            new EngineDefinition(
                id: 'openvoice_v2',
                displayName: 'OpenVoice V2',
                capability: EngineCatalogCapability::VoiceClone,
                family: EngineFamily::VoiceClone,
                role: EngineCatalogRole::Default,
                binaryName: 'openvoice',
                modelPath: 'openvoice',
                expectedModel: 'openvoice_v2',
                installedMode: EngineExecutionMode::Real,
            ),
            new EngineDefinition(
                id: 'chatterbox',
                displayName: 'Chatterbox',
                capability: EngineCatalogCapability::VoiceClone,
                family: EngineFamily::VoiceClone,
                role: EngineCatalogRole::Alternative1,
                binaryName: 'chatterbox',
                modelPath: 'chatterbox',
            ),
            new EngineDefinition(
                id: 'xtts_v2',
                displayName: 'XTTS-v2',
                capability: EngineCatalogCapability::VoiceClone,
                family: EngineFamily::VoiceClone,
                role: EngineCatalogRole::Alternative2,
                binaryName: 'xtts',
                modelPath: 'xtts',
            ),

            // Lip Sync
            new EngineDefinition(
                id: 'latentsync',
                displayName: 'LatentSync',
                capability: EngineCatalogCapability::LipSync,
                family: EngineFamily::LipSync,
                role: EngineCatalogRole::Default,
                tier: EngineCatalogTier::PremiumNvidia,
                binaryName: 'latentsync',
                modelPath: 'latentsync',
                expectedModel: 'latentsync',
                installedMode: EngineExecutionMode::Real,
            ),
            new EngineDefinition(
                id: 'liveportrait',
                displayName: 'LivePortrait',
                capability: EngineCatalogCapability::LipSync,
                family: EngineFamily::LivePortrait,
                role: EngineCatalogRole::Alternative1,
                tier: EngineCatalogTier::Alternative,
                binaryName: 'liveportrait',
                modelPath: 'liveportrait',
            ),
            new EngineDefinition(
                id: 'wav2lip',
                displayName: 'Wav2Lip',
                capability: EngineCatalogCapability::LipSync,
                family: EngineFamily::LipSync,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::CpuAlternative,
                binaryName: 'wav2lip',
                modelPath: 'wav2lip',
            ),
            new EngineDefinition(
                id: 'musetalk',
                displayName: 'MuseTalk',
                capability: EngineCatalogCapability::LipSync,
                family: EngineFamily::LipSync,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Legacy,
                binaryName: 'musetalk',
                modelPath: 'musetalk',
            ),
            new EngineDefinition(
                id: 'echomimic_v2',
                displayName: 'EchoMimic V2',
                capability: EngineCatalogCapability::LipSync,
                family: EngineFamily::LipSync,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Experimental,
                binaryName: 'echomimic',
                modelPath: 'echomimic',
            ),

            // Video Render
            new EngineDefinition(
                id: 'ffmpeg',
                displayName: 'FFmpeg',
                capability: EngineCatalogCapability::VideoRender,
                family: EngineFamily::Ffmpeg,
                role: EngineCatalogRole::Default,
                binaryName: 'ffmpeg',
                requiresModelFiles: false,
            ),
            new EngineDefinition(
                id: 'ffmpeg_nvenc',
                displayName: 'FFmpeg NVENC',
                capability: EngineCatalogCapability::VideoRender,
                family: EngineFamily::Ffmpeg,
                role: EngineCatalogRole::Alternative1,
                binaryName: 'ffmpeg',
                requiresModelFiles: false,
                expectedModel: 'h264_nvenc',
            ),
            new EngineDefinition(
                id: 'ffmpeg_av1',
                displayName: 'FFmpeg AV1',
                capability: EngineCatalogCapability::VideoRender,
                family: EngineFamily::Ffmpeg,
                role: EngineCatalogRole::Alternative2,
                binaryName: 'ffmpeg',
                requiresModelFiles: false,
                expectedModel: 'libaom-av1',
            ),

            // OCR
            new EngineDefinition(
                id: 'paddleocr',
                displayName: 'PaddleOCR',
                capability: EngineCatalogCapability::Ocr,
                family: EngineFamily::Ocr,
                role: EngineCatalogRole::Default,
                binaryName: 'paddleocr',
                modelPath: 'paddleocr',
            ),
            new EngineDefinition(
                id: 'easyocr',
                displayName: 'EasyOCR',
                capability: EngineCatalogCapability::Ocr,
                family: EngineFamily::Ocr,
                role: EngineCatalogRole::Alternative1,
                binaryName: 'easyocr',
                modelPath: 'easyocr',
            ),

            // Vision
            new EngineDefinition(
                id: 'florence_2',
                displayName: 'Florence-2',
                capability: EngineCatalogCapability::Vision,
                family: EngineFamily::Vision,
                role: EngineCatalogRole::Default,
                binaryName: 'florence-2',
                modelPath: 'florence-2',
            ),
            new EngineDefinition(
                id: 'qwen2_5_vl',
                displayName: 'Qwen2.5-VL',
                capability: EngineCatalogCapability::Vision,
                family: EngineFamily::Vision,
                role: EngineCatalogRole::Alternative1,
                binaryName: 'qwen2-vl',
                modelPath: 'qwen2-vl',
            ),
            new EngineDefinition(
                id: 'smolvlm',
                displayName: 'SmolVLM',
                capability: EngineCatalogCapability::Vision,
                family: EngineFamily::Vision,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Lightweight,
                binaryName: 'smolvlm',
                modelPath: 'smolvlm',
            ),

            // Embeddings
            new EngineDefinition(
                id: 'bge_m3',
                displayName: 'BGE-M3',
                capability: EngineCatalogCapability::Embeddings,
                family: EngineFamily::Embeddings,
                role: EngineCatalogRole::Default,
                binaryName: 'bge-m3',
                modelPath: 'bge-m3',
            ),
            new EngineDefinition(
                id: 'nomic_embed',
                displayName: 'Nomic Embed',
                capability: EngineCatalogCapability::Embeddings,
                family: EngineFamily::Embeddings,
                role: EngineCatalogRole::Alternative1,
                binaryName: 'nomic-embed',
                modelPath: 'nomic-embed',
            ),
            new EngineDefinition(
                id: 'jina_embeddings',
                displayName: 'Jina Embeddings',
                capability: EngineCatalogCapability::Embeddings,
                family: EngineFamily::Embeddings,
                role: EngineCatalogRole::Alternative2,
                binaryName: 'jina-embeddings',
                modelPath: 'jina-embeddings',
            ),
            new EngineDefinition(
                id: 'e5_large',
                displayName: 'E5 Large',
                capability: EngineCatalogCapability::Embeddings,
                family: EngineFamily::Embeddings,
                role: EngineCatalogRole::Alternative2,
                tier: EngineCatalogTier::Alternative,
                binaryName: 'e5-embed',
                modelPath: 'e5-large',
            ),

            // Reranking
            new EngineDefinition(
                id: 'bge_reranker',
                displayName: 'BGE Reranker',
                capability: EngineCatalogCapability::Reranking,
                family: EngineFamily::Reranker,
                role: EngineCatalogRole::Default,
                binaryName: 'bge-reranker',
                modelPath: 'bge-reranker',
            ),
            new EngineDefinition(
                id: 'jina_reranker',
                displayName: 'Jina Reranker',
                capability: EngineCatalogCapability::Reranking,
                family: EngineFamily::Reranker,
                role: EngineCatalogRole::Alternative1,
                binaryName: 'jina-reranker',
                modelPath: 'jina-reranker',
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

    /**
     * @return list<EngineDefinition>
     */
    public static function forCapability(EngineCatalogCapability $capability): array
    {
        return array_values(array_filter(
            self::all(),
            static fn (EngineDefinition $definition): bool => $definition->capability === $capability,
        ));
    }
}

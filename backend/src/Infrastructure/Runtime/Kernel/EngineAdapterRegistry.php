<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Kernel;

use App\Infrastructure\AI\AIEngineRegistryFactory;

final class EngineAdapterRegistry
{
    /**
     * @var array<string, string>
     */
    private const array ENGINE_TO_ADAPTER = [
        'faster_whisper_large_v3' => AIEngineRegistryFactory::PROVIDER_FASTER_WHISPER,
        'whisper_cpp' => 'whisper_cpp',
        'parakeet' => 'parakeet',
        'canary' => 'canary',
        'ollama_gemma3' => AIEngineRegistryFactory::PROVIDER_OLLAMA,
        'ollama_qwen3' => AIEngineRegistryFactory::PROVIDER_OLLAMA,
        'ollama_deepseek_r1_distill' => AIEngineRegistryFactory::PROVIDER_OLLAMA,
        'f5_tts' => AIEngineRegistryFactory::PROVIDER_F5_TTS,
        'kokoro' => AIEngineRegistryFactory::PROVIDER_KOKORO,
        'piper' => 'piper',
        'dia' => 'dia',
        'openvoice_v2' => AIEngineRegistryFactory::PROVIDER_OPENVOICE,
        'chatterbox' => 'chatterbox',
        'xtts_v2' => AIEngineRegistryFactory::PROVIDER_XTTS,
        'latentsync' => AIEngineRegistryFactory::PROVIDER_LATENTSYNC,
        'liveportrait' => 'liveportrait',
        'wav2lip' => AIEngineRegistryFactory::PROVIDER_WAV2LIP,
        'musetalk' => 'musetalk',
        'echomimic_v2' => 'echomimic_v2',
        'ffmpeg' => AIEngineRegistryFactory::PROVIDER_FFMPEG,
        'ffmpeg_nvenc' => AIEngineRegistryFactory::PROVIDER_FFMPEG,
        'ffmpeg_av1' => AIEngineRegistryFactory::PROVIDER_FFMPEG,
    ];

    /**
     * @var array<string, string>
     */
    private const array LEGACY_PROVIDER_TO_ENGINE = [
        AIEngineRegistryFactory::PROVIDER_FASTER_WHISPER => 'faster_whisper_large_v3',
        AIEngineRegistryFactory::PROVIDER_OLLAMA => 'ollama_gemma3',
        AIEngineRegistryFactory::PROVIDER_F5_TTS => 'f5_tts',
        AIEngineRegistryFactory::PROVIDER_KOKORO => 'kokoro',
        AIEngineRegistryFactory::PROVIDER_XTTS => 'xtts_v2',
        AIEngineRegistryFactory::PROVIDER_OPENVOICE => 'openvoice_v2',
        AIEngineRegistryFactory::PROVIDER_SEEDVC => 'chatterbox',
        AIEngineRegistryFactory::PROVIDER_LATENTSYNC => 'latentsync',
        AIEngineRegistryFactory::PROVIDER_WAV2LIP => 'wav2lip',
        AIEngineRegistryFactory::PROVIDER_FFMPEG => 'ffmpeg',
    ];

    public function adapterKeyForEngine(string $engineId): string
    {
        return self::ENGINE_TO_ADAPTER[$engineId] ?? $engineId;
    }

    public function engineIdForLegacyProvider(string $providerId): string
    {
        return self::LEGACY_PROVIDER_TO_ENGINE[$providerId] ?? $providerId;
    }

    public function engineIdForLegacyProviderAndCapability(
        string $providerId,
        string $capability,
    ): string {
        if (AIEngineRegistryFactory::PROVIDER_OLLAMA === $providerId) {
            return 'ollama_gemma3';
        }

        return $this->engineIdForLegacyProvider($providerId);
    }
}

<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Hardware\HardwareRequirement;

final class EngineRequirementMatrix
{
    /**
     * @return list<HardwareRequirement>
     */
    public static function all(): array
    {
        return [
            // Speech-to-Text
            new HardwareRequirement('faster_whisper_large_v3', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('whisper_cpp', cpuFallbackSupported: true, minimumRamGb: 2.0),
            new HardwareRequirement('parakeet', requiredGpuVendor: 'NVIDIA', cudaRequired: true, cpuFallbackSupported: false, minimumRamGb: 8.0),
            new HardwareRequirement('canary', requiredGpuVendor: 'NVIDIA', cudaRequired: true, cpuFallbackSupported: false, minimumRamGb: 8.0),

            // Translation
            new HardwareRequirement('ollama_gemma3', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('ollama_qwen3', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('ollama_deepseek_r1_distill', cpuFallbackSupported: true, minimumRamGb: 6.0),

            // TTS
            new HardwareRequirement('f5_tts', cudaRecommended: true, cpuFallbackSupported: true, minimumRamGb: 6.0),
            new HardwareRequirement('kokoro', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('piper', cpuFallbackSupported: true, minimumRamGb: 2.0),
            new HardwareRequirement('dia', cudaRecommended: true, cpuFallbackSupported: false, minimumRamGb: 8.0),

            // Voice Clone
            new HardwareRequirement(
                'openvoice_v2',
                cudaRecommended: true,
                cpuFallbackSupported: true,
                minimumRamGb: 6.0,
                optionalLanguagePacks: ['unidic'],
                documentationLink: '/docs/operations/OPENVOICE_INSTALLATION.md',
            ),
            new HardwareRequirement('chatterbox', cudaRecommended: true, cpuFallbackSupported: true, minimumRamGb: 6.0),
            new HardwareRequirement('xtts_v2', cudaRecommended: true, cpuFallbackSupported: true, minimumRamGb: 8.0),

            // Lip Sync
            new HardwareRequirement(
                'latentsync',
                requiredGpuVendor: 'NVIDIA',
                cudaRequired: true,
                minimumVramGb: 18.0,
                cpuFallbackSupported: false,
                minimumRamGb: 16.0,
                documentationLink: '/docs/operations/LATENTSYNC_INSTALLATION.md',
            ),
            new HardwareRequirement(
                'echomimic_v2',
                requiredGpuVendor: 'NVIDIA',
                cudaRequired: true,
                minimumVramGb: 12.0,
                cpuFallbackSupported: false,
                minimumRamGb: 16.0,
            ),
            new HardwareRequirement(
                'liveportrait',
                requiredGpuVendor: 'NVIDIA',
                cudaRequired: true,
                minimumVramGb: 8.0,
                cpuFallbackSupported: false,
                minimumRamGb: 12.0,
            ),
            new HardwareRequirement(
                'wav2lip',
                cudaRecommended: true,
                cpuFallbackSupported: true,
                minimumRamGb: 6.0,
            ),
            new HardwareRequirement(
                'musetalk',
                requiredGpuVendor: 'NVIDIA',
                cudaRequired: true,
                minimumVramGb: 8.0,
                cpuFallbackSupported: false,
                minimumRamGb: 12.0,
            ),

            // Video Render
            new HardwareRequirement('ffmpeg', cpuFallbackSupported: true),
            new HardwareRequirement('ffmpeg_nvenc', requiredGpuVendor: 'NVIDIA', nvencRequired: true, cpuFallbackSupported: false),
            new HardwareRequirement('ffmpeg_av1', cpuFallbackSupported: true, minimumRamGb: 4.0),

            // OCR
            new HardwareRequirement('paddleocr', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('easyocr', cudaRecommended: true, cpuFallbackSupported: true, minimumRamGb: 4.0),

            // Vision
            new HardwareRequirement('florence_2', cudaRecommended: true, cpuFallbackSupported: true, minimumRamGb: 8.0),
            new HardwareRequirement('qwen2_5_vl', requiredGpuVendor: 'NVIDIA', cudaRequired: true, minimumVramGb: 12.0, cpuFallbackSupported: false, minimumRamGb: 16.0),
            new HardwareRequirement('smolvlm', cpuFallbackSupported: true, minimumRamGb: 4.0),

            // Embeddings
            new HardwareRequirement('bge_m3', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('nomic_embed', cpuFallbackSupported: true, minimumRamGb: 2.0),
            new HardwareRequirement('jina_embeddings', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('e5_large', cpuFallbackSupported: true, minimumRamGb: 6.0),

            // Reranking
            new HardwareRequirement('bge_reranker', cpuFallbackSupported: true, minimumRamGb: 4.0),
            new HardwareRequirement('jina_reranker', cpuFallbackSupported: true, minimumRamGb: 4.0),
        ];
    }

    public static function findByEngineId(string $engineId): ?HardwareRequirement
    {
        foreach (self::all() as $requirement) {
            if ($requirement->engineId === $engineId) {
                return $requirement;
            }
        }

        return null;
    }
}

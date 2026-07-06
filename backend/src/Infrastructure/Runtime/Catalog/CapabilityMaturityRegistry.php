<?php

declare(strict_types=1);

namespace App\Infrastructure\Runtime\Catalog;

use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Hardware\HardwareProvider;
use App\Domain\Runtime\CapabilityMaturityLevel;

final class CapabilityMaturityRegistry
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $entries = [];

        foreach (EngineCatalogCapability::cases() as $capability) {
            $definitions = EngineCatalogDefinitions::forCapability($capability);
            $default = EngineCatalogDefinitions::defaultForCapability($capability);
            $maturity = self::maturityFor($capability);

            $engines = [];
            foreach ($definitions as $definition) {
                $requirement = EngineRequirementMatrix::findByEngineId($definition->id);
                $engines[] = [
                    'id' => $definition->id,
                    'displayName' => $definition->displayName,
                    'role' => $definition->role->value,
                    'roleLabel' => $definition->role->label(),
                    'tier' => $definition->tier->value,
                    'tierLabel' => $definition->tier->label(),
                    'hardware' => self::hardwareSummary($requirement),
                    'provider' => HardwareProvider::Host->value,
                    'providerLabel' => HardwareProvider::Host->label(),
                    'benchmarkModel' => self::benchmarkModelFor($definition->id),
                ];
            }

            $entries[] = [
                'capability' => $capability->value,
                'label' => $capability->label(),
                'maturity' => $maturity->value,
                'maturityLabel' => $maturity->label(),
                'videoPipeline' => $capability->isVideoPipeline(),
                'defaultEngineId' => $default?->id,
                'defaultDisplayName' => $default?->displayName,
                'engineCount' => count($engines),
                'engines' => $engines,
            ];
        }

        return $entries;
    }

    private static function maturityFor(EngineCatalogCapability $capability): CapabilityMaturityLevel
    {
        return match ($capability) {
            EngineCatalogCapability::SpeechToText,
            EngineCatalogCapability::Translation,
            EngineCatalogCapability::TextToSpeech,
            EngineCatalogCapability::VoiceClone,
            EngineCatalogCapability::VideoRender => CapabilityMaturityLevel::Stable,
            EngineCatalogCapability::LipSync => CapabilityMaturityLevel::Beta,
            EngineCatalogCapability::Ocr,
            EngineCatalogCapability::Embeddings,
            EngineCatalogCapability::Reranking => CapabilityMaturityLevel::Beta,
            EngineCatalogCapability::Vision => CapabilityMaturityLevel::Experimental,
        };
    }

    private static function benchmarkModelFor(string $engineId): string
    {
        return match ($engineId) {
            'faster_whisper_large_v3', 'whisper_cpp' => 'librispeech-clean-5min',
            'ollama_gemma3', 'ollama_qwen3', 'ollama_deepseek_r1_distill' => 'wmt14-en-fr-100',
            'f5_tts', 'kokoro', 'piper', 'dia' => 'ljspeech-1min',
            'openvoice_v2', 'chatterbox', 'xtts_v2' => 'voice-clone-reference-30s',
            'latentsync', 'liveportrait', 'wav2lip', 'musetalk', 'echomimic_v2' => 'talking-head-10s',
            'ffmpeg', 'ffmpeg_nvenc', 'ffmpeg_av1' => 'encode-1080p-30s',
            'paddleocr', 'easyocr' => 'document-page-scan',
            'florence_2', 'qwen2_5_vl', 'smolvlm' => 'coco-caption-10',
            'bge_m3', 'nomic_embed', 'jina_embeddings', 'e5_large' => 'msmarco-100-queries',
            'bge_reranker', 'jina_reranker' => 'msmarco-rerank-50',
            default => 'smoke-probe',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function hardwareSummary(?\App\Domain\Hardware\HardwareRequirement $requirement): array
    {
        if (null === $requirement) {
            return ['cpuFallback' => true];
        }

        return [
            'cudaRequired' => $requirement->cudaRequired,
            'cudaRecommended' => $requirement->cudaRecommended,
            'cpuFallback' => $requirement->cpuFallbackSupported,
            'minimumRamGb' => $requirement->minimumRamGb,
            'minimumVramGb' => $requirement->minimumVramGb,
            'requiredGpuVendor' => $requirement->requiredGpuVendor,
        ];
    }
}

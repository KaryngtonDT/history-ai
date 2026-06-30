<?php

declare(strict_types=1);

namespace App\Infrastructure\Orchestrator;

use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineRegistry;
use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Orchestrator\PipelineRecommendation;
use App\Domain\Orchestrator\PipelineRecommendationId;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Orchestrator\VideoAnalysis;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineDefaultProviders;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;

final class DeterministicPipelinePlanner implements PipelinePlannerInterface
{
    private const float LOW_VRAM_THRESHOLD_GB = 4.0;

    private const float MEDIUM_VRAM_THRESHOLD_GB = 8.0;

    public function __construct(
        private readonly AIEngineRegistry $registry,
    ) {
    }

    public function recommend(VideoAnalysis $analysis): PipelineRecommendation
    {
        return $this->recommendWithStrategy($analysis, $this->resolveStrategy($analysis));
    }

    public function recommendWithStrategy(
        VideoAnalysis $analysis,
        ProcessingStrategy $strategy,
    ): PipelineRecommendation {
        $stages = [
            PipelineStage::create(
                PipelineStageType::SpeechToText,
                $this->selectProvider(
                    AIEngineCapability::SpeechToText,
                    $strategy,
                    [PipelineDefaultProviders::SPEECH_TO_TEXT],
                ),
            ),
            PipelineStage::create(
                PipelineStageType::Translation,
                $this->selectProvider(
                    AIEngineCapability::Translation,
                    $strategy,
                    [PipelineDefaultProviders::TRANSLATION],
                ),
            ),
            PipelineStage::create(
                PipelineStageType::TextToSpeech,
                $this->selectProvider(
                    AIEngineCapability::TextToSpeech,
                    $strategy,
                    $this->textToSpeechPreferences($strategy),
                ),
            ),
            PipelineStage::create(
                PipelineStageType::VoiceClone,
                $this->selectProvider(
                    AIEngineCapability::VoiceClone,
                    $strategy,
                    $this->voiceClonePreferences($strategy),
                ),
            ),
            PipelineStage::create(
                PipelineStageType::LipSync,
                $this->selectProvider(
                    AIEngineCapability::LipSync,
                    $strategy,
                    $this->lipSyncPreferences($strategy),
                ),
            ),
            PipelineStage::create(
                PipelineStageType::VideoRender,
                $this->selectProvider(
                    AIEngineCapability::VideoRender,
                    $strategy,
                    [PipelineDefaultProviders::VIDEO_RENDER],
                ),
            ),
        ];

        $configuration = PipelineConfiguration::create(
            PipelineConfigurationId::generate(),
            $stages,
        );

        return PipelineRecommendation::create(
            PipelineRecommendationId::generate(),
            $strategy,
            $configuration,
            $this->buildExplanation($analysis, $strategy),
            $this->estimateDurationSeconds($analysis, $strategy),
            $this->estimateQuality($strategy),
            $this->estimateVramGb($analysis, $strategy),
        );
    }

    private function resolveStrategy(VideoAnalysis $analysis): ProcessingStrategy
    {
        if (!$analysis->gpuAvailable()) {
            return ProcessingStrategy::Speed;
        }

        if ($analysis->estimatedVramGb() < self::LOW_VRAM_THRESHOLD_GB) {
            return ProcessingStrategy::LowMemory;
        }

        if ($analysis->estimatedVramGb() < self::MEDIUM_VRAM_THRESHOLD_GB) {
            return ProcessingStrategy::Speed;
        }

        return ProcessingStrategy::Balanced;
    }

    /**
     * @param list<string> $preferences
     */
    private function selectProvider(
        AIEngineCapability $capability,
        ProcessingStrategy $strategy,
        array $preferences,
    ): string {
        $enabledIds = array_map(
            static fn ($provider) => $provider->providerId(),
            $this->registry->enabledProviders($capability),
        );

        foreach ($preferences as $preferredId) {
            if (in_array($preferredId, $enabledIds, true)) {
                return $preferredId;
            }
        }

        if ([] !== $enabledIds) {
            return $enabledIds[0];
        }

        return match ($capability) {
            AIEngineCapability::SpeechToText => PipelineDefaultProviders::SPEECH_TO_TEXT,
            AIEngineCapability::Translation => PipelineDefaultProviders::TRANSLATION,
            AIEngineCapability::TextToSpeech => PipelineDefaultProviders::TEXT_TO_SPEECH,
            AIEngineCapability::VoiceClone => PipelineDefaultProviders::VOICE_CLONE,
            AIEngineCapability::LipSync => PipelineDefaultProviders::LIP_SYNC,
            AIEngineCapability::VideoRender => PipelineDefaultProviders::VIDEO_RENDER,
        };
    }

    /**
     * @return list<string>
     */
    private function textToSpeechPreferences(ProcessingStrategy $strategy): array
    {
        return match ($strategy) {
            ProcessingStrategy::Quality => ['f5_tts'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['kokoro', 'f5_tts'],
            ProcessingStrategy::Balanced => ['f5_tts', 'kokoro'],
        };
    }

    /**
     * @return list<string>
     */
    private function voiceClonePreferences(ProcessingStrategy $strategy): array
    {
        return match ($strategy) {
            ProcessingStrategy::Quality, ProcessingStrategy::Balanced => ['openvoice', 'seedvc'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['seedvc', 'openvoice'],
        };
    }

    /**
     * @return list<string>
     */
    private function lipSyncPreferences(ProcessingStrategy $strategy): array
    {
        return match ($strategy) {
            ProcessingStrategy::Quality, ProcessingStrategy::Balanced => ['latentsync', 'wav2lip'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['wav2lip', 'latentsync'],
        };
    }

    private function buildExplanation(VideoAnalysis $analysis, ProcessingStrategy $strategy): string
    {
        $language = $analysis->detectedLanguage();

        return match ($strategy) {
            ProcessingStrategy::Quality => sprintf(
                'Quality-first pipeline for %s content with %.0f seconds of video.',
                $language,
                $analysis->durationSeconds(),
            ),
            ProcessingStrategy::Speed => sprintf(
                'Speed-optimized pipeline for %s content (GPU %s).',
                $language,
                $analysis->gpuAvailable() ? 'available' : 'unavailable',
            ),
            ProcessingStrategy::LowMemory => sprintf(
                'Low-memory pipeline for %s content with %.1f GB estimated VRAM.',
                $language,
                $analysis->estimatedVramGb(),
            ),
            ProcessingStrategy::Balanced => sprintf(
                'Balanced pipeline for %s content targeting French and German translations.',
                $language,
            ),
        };
    }

    private function estimateDurationSeconds(VideoAnalysis $analysis, ProcessingStrategy $strategy): int
    {
        $multiplier = match ($strategy) {
            ProcessingStrategy::Quality => 2.5,
            ProcessingStrategy::Speed => 1.2,
            ProcessingStrategy::LowMemory => 1.4,
            ProcessingStrategy::Balanced => 1.8,
        };

        return (int) max(60, round($analysis->durationSeconds() * $multiplier));
    }

    private function estimateQuality(ProcessingStrategy $strategy): int
    {
        return match ($strategy) {
            ProcessingStrategy::Quality => 5,
            ProcessingStrategy::Balanced => 4,
            ProcessingStrategy::Speed => 3,
            ProcessingStrategy::LowMemory => 3,
        };
    }

    private function estimateVramGb(VideoAnalysis $analysis, ProcessingStrategy $strategy): float
    {
        if (!$analysis->gpuAvailable()) {
            return 0.0;
        }

        return match ($strategy) {
            ProcessingStrategy::Quality => max($analysis->estimatedVramGb(), 12.0),
            ProcessingStrategy::Balanced => max($analysis->estimatedVramGb(), 8.0),
            ProcessingStrategy::Speed => min($analysis->estimatedVramGb(), 6.0),
            ProcessingStrategy::LowMemory => min($analysis->estimatedVramGb(), 4.0),
        };
    }
}

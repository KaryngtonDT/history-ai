<?php

declare(strict_types=1);

namespace App\Infrastructure\Orchestrator;

use App\Domain\AI\AIEngineCapability;
use App\Domain\AI\AIEngineRegistry;
use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Orchestrator\PipelineRecommendation;
use App\Domain\Orchestrator\PipelineRecommendationId;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineDefaultProviders;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Review\LipSyncStrengthPreference;
use App\Domain\Review\RenderingPresetPreference;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\VoiceStabilityPreference;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\VideoIntelligence;

final class DeterministicPipelinePlanner implements PipelinePlannerInterface
{
    private const float LOW_VRAM_THRESHOLD_GB = 4.0;

    private const float MEDIUM_VRAM_THRESHOLD_GB = 8.0;

    private const float LONG_DURATION_SECONDS = 1800.0;

    public function __construct(
        private readonly AIEngineRegistry $registry,
    ) {
    }

    public function recommend(
        VideoIntelligence $intelligence,
        ?UserPreferenceProfile $preferences = null,
    ): PipelineRecommendation {
        return $this->recommendWithStrategy(
            $intelligence,
            $this->resolveStrategy($intelligence, $preferences),
            $preferences,
        );
    }

    public function recommendWithStrategy(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        ?UserPreferenceProfile $preferences = null,
    ): PipelineRecommendation {
        $stages = [
            PipelineStage::create(
                PipelineStageType::SpeechToText,
                $this->selectProvider(
                    AIEngineCapability::SpeechToText,
                    $strategy,
                    $this->speechToTextPreferences($intelligence, $strategy),
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
                    $this->textToSpeechPreferences($intelligence, $strategy),
                ),
            ),
            PipelineStage::create(
                PipelineStageType::VoiceClone,
                $this->selectProvider(
                    AIEngineCapability::VoiceClone,
                    $strategy,
                    $this->voiceClonePreferences($intelligence, $strategy, $preferences),
                ),
            ),
            PipelineStage::create(
                PipelineStageType::LipSync,
                $this->selectProvider(
                    AIEngineCapability::LipSync,
                    $strategy,
                    $this->lipSyncPreferences($intelligence, $strategy, $preferences),
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

        $reasons = $this->buildReasons($intelligence, $strategy, $configuration, $preferences);

        return PipelineRecommendation::create(
            PipelineRecommendationId::generate(),
            $strategy,
            $configuration,
            $this->buildExplanation($intelligence, $strategy, $preferences),
            $this->estimateDurationSeconds($intelligence, $strategy),
            $this->estimateQuality($strategy, $intelligence),
            $this->estimateVramGb($intelligence, $strategy),
            $reasons,
        );
    }

    private function resolveStrategy(
        VideoIntelligence $intelligence,
        ?UserPreferenceProfile $preferences = null,
    ): ProcessingStrategy {
        $baseStrategy = $this->resolveBaseStrategy($intelligence);

        if (null === $preferences || !$intelligence->gpuAvailable()) {
            return $baseStrategy;
        }

        return match ($preferences->renderingPreset()) {
            RenderingPresetPreference::Quality => ProcessingStrategy::Quality,
            RenderingPresetPreference::Speed => $this->preferSpeedWhenCompatible($intelligence, $baseStrategy),
            RenderingPresetPreference::Balanced => $baseStrategy,
        };
    }

    private function resolveBaseStrategy(VideoIntelligence $intelligence): ProcessingStrategy
    {
        if (!$intelligence->gpuAvailable()) {
            return ProcessingStrategy::Speed;
        }

        if ($intelligence->estimatedVramGb() < self::LOW_VRAM_THRESHOLD_GB) {
            return ProcessingStrategy::LowMemory;
        }

        if ($intelligence->audio()->confidence()->isLow()) {
            return ProcessingStrategy::Quality;
        }

        if ($intelligence->durationSeconds() > self::LONG_DURATION_SECONDS) {
            return ProcessingStrategy::Speed;
        }

        if ($intelligence->visual()->lighting() === LightingCondition::Poor) {
            return ProcessingStrategy::Quality;
        }

        if ($intelligence->estimatedVramGb() < self::MEDIUM_VRAM_THRESHOLD_GB) {
            return ProcessingStrategy::Speed;
        }

        if ($intelligence->audio()->backgroundMusic() === BackgroundMusic::Detected) {
            return ProcessingStrategy::Quality;
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
    private function speechToTextPreferences(VideoIntelligence $intelligence, ProcessingStrategy $strategy): array
    {
        if ($intelligence->audio()->confidence()->isLow()) {
            return ['faster_whisper'];
        }

        return match ($strategy) {
            ProcessingStrategy::Quality => ['faster_whisper'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['faster_whisper'],
            ProcessingStrategy::Balanced => ['faster_whisper'],
        };
    }

    /**
     * @return list<string>
     */
    private function textToSpeechPreferences(VideoIntelligence $intelligence, ProcessingStrategy $strategy): array
    {
        if ($intelligence->speech()->pauseCount() > 20) {
            return ['f5_tts', 'kokoro'];
        }

        return match ($strategy) {
            ProcessingStrategy::Quality => ['f5_tts'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['kokoro', 'f5_tts'],
            ProcessingStrategy::Balanced => ['f5_tts', 'kokoro'],
        };
    }

    /**
     * @return list<string>
     */
    private function voiceClonePreferences(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        ?UserPreferenceProfile $preferences = null,
    ): array {
        if (null !== $preferences) {
            return match ($preferences->voiceStability()) {
                VoiceStabilityPreference::High => ['openvoice', 'seedvc'],
                VoiceStabilityPreference::Low => ['seedvc', 'openvoice'],
                VoiceStabilityPreference::Medium => ['openvoice', 'seedvc'],
            };
        }

        if ($intelligence->audio()->speakerCount() >= 2 || $intelligence->audio()->backgroundMusic() === BackgroundMusic::Detected) {
            return ['openvoice', 'seedvc'];
        }

        return match ($strategy) {
            ProcessingStrategy::Quality, ProcessingStrategy::Balanced => ['openvoice', 'seedvc'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['seedvc', 'openvoice'],
        };
    }

    /**
     * @return list<string>
     */
    private function lipSyncPreferences(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        ?UserPreferenceProfile $preferences = null,
    ): array {
        if (null !== $preferences) {
            return match ($preferences->lipSyncStrength()) {
                LipSyncStrengthPreference::Subtle => ['wav2lip', 'latentsync'],
                LipSyncStrengthPreference::Strong => ['latentsync', 'wav2lip'],
                LipSyncStrengthPreference::Moderate => ['latentsync', 'wav2lip'],
            };
        }

        if (in_array($intelligence->visual()->lipVisibility(), [LipVisibility::Poor, LipVisibility::Partial], true)) {
            return ['wav2lip', 'latentsync'];
        }

        return match ($strategy) {
            ProcessingStrategy::Quality, ProcessingStrategy::Balanced => ['latentsync', 'wav2lip'],
            ProcessingStrategy::Speed, ProcessingStrategy::LowMemory => ['wav2lip', 'latentsync'],
        };
    }

    private function preferSpeedWhenCompatible(
        VideoIntelligence $intelligence,
        ProcessingStrategy $fallback,
    ): ProcessingStrategy {
        if ($intelligence->estimatedVramGb() < self::LOW_VRAM_THRESHOLD_GB) {
            return ProcessingStrategy::LowMemory;
        }

        if ($intelligence->audio()->confidence()->isLow()) {
            return $fallback;
        }

        return ProcessingStrategy::Speed;
    }

    private function buildExplanation(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        ?UserPreferenceProfile $preferences = null,
    ): string {
        if (null !== $preferences) {
            return $preferences->explanationLines()[0];
        }

        $language = $intelligence->audio()->language();

        return match ($strategy) {
            ProcessingStrategy::Quality => sprintf(
                'Quality-first pipeline for %s content with %d%% STT confidence.',
                $language,
                $intelligence->audio()->confidence()->percentage(),
            ),
            ProcessingStrategy::Speed => sprintf(
                'Speed-optimized pipeline for %s content (GPU %s).',
                $language,
                $intelligence->gpuAvailable() ? 'available' : 'unavailable',
            ),
            ProcessingStrategy::LowMemory => sprintf(
                'Low-memory pipeline for %s content with %.1f GB estimated VRAM.',
                $language,
                $intelligence->estimatedVramGb(),
            ),
            ProcessingStrategy::Balanced => sprintf(
                'Balanced pipeline for %s %s scene with %d speaker(s).',
                $language,
                $intelligence->scene()->value,
                $intelligence->audio()->speakerCount(),
            ),
        };
    }

    /**
     * @return list<string>
     */
    private function buildReasons(
        VideoIntelligence $intelligence,
        ProcessingStrategy $strategy,
        PipelineConfiguration $configuration,
        ?UserPreferenceProfile $preferences = null,
    ): array {
        $reasons = [];

        if (null !== $preferences) {
            foreach ($preferences->explanationLines() as $line) {
                $reasons[] = $line;
            }
        }

        if ($intelligence->audio()->speakerCount() >= 2) {
            $reasons[] = sprintf(
                '%d speakers detected.',
                $intelligence->audio()->speakerCount(),
            );
        }

        if ($intelligence->audio()->confidence()->isHigh()) {
            $reasons[] = 'High STT confidence.';
        } elseif ($intelligence->audio()->confidence()->isLow()) {
            $reasons[] = sprintf(
                'STT confidence is %d%%; a more robust pass is recommended.',
                $intelligence->audio()->confidence()->percentage(),
            );
        }

        if ($intelligence->audio()->backgroundMusic() === BackgroundMusic::Detected) {
            $reasons[] = 'Background music detected; stronger voice clone recommended.';
        }

        if ($intelligence->visual()->lighting() === LightingCondition::Good
            || $intelligence->visual()->lighting() === LightingCondition::Excellent) {
            $reasons[] = 'Good lighting detected.';
        }

        if ($intelligence->visual()->lipVisibility() === LipVisibility::Excellent) {
            $reasons[] = 'Excellent lip visibility; LatentSync preferred.';
        }

        $voiceCloneProvider = $configuration->providerFor(PipelineStageType::VoiceClone);
        if ('openvoice' === $voiceCloneProvider) {
            $reasons[] = 'OpenVoice recommended for multi-speaker or music-heavy content.';
        }

        $reasons[] = sprintf('%s strategy selected.', ucfirst($strategy->value));

        return $reasons;
    }

    private function estimateDurationSeconds(VideoIntelligence $intelligence, ProcessingStrategy $strategy): int
    {
        $multiplier = match ($strategy) {
            ProcessingStrategy::Quality => 2.5,
            ProcessingStrategy::Speed => 1.2,
            ProcessingStrategy::LowMemory => 1.4,
            ProcessingStrategy::Balanced => 1.8,
        };

        return (int) max(60, round($intelligence->durationSeconds() * $multiplier));
    }

    private function estimateQuality(ProcessingStrategy $strategy, VideoIntelligence $intelligence): int
    {
        $base = match ($strategy) {
            ProcessingStrategy::Quality => 5,
            ProcessingStrategy::Balanced => 4,
            ProcessingStrategy::Speed => 3,
            ProcessingStrategy::LowMemory => 3,
        };

        if ($intelligence->audio()->confidence()->isHigh() && $base < 5) {
            return min(5, $base + 1);
        }

        return $base;
    }

    private function estimateVramGb(VideoIntelligence $intelligence, ProcessingStrategy $strategy): float
    {
        if (!$intelligence->gpuAvailable()) {
            return 0.0;
        }

        return match ($strategy) {
            ProcessingStrategy::Quality => max($intelligence->estimatedVramGb(), 12.0),
            ProcessingStrategy::Balanced => max($intelligence->estimatedVramGb(), 8.0),
            ProcessingStrategy::Speed => min($intelligence->estimatedVramGb(), 6.0),
            ProcessingStrategy::LowMemory => min($intelligence->estimatedVramGb(), 4.0),
        };
    }
}

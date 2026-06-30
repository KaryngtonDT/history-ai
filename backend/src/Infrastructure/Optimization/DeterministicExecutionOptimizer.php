<?php

declare(strict_types=1);

namespace App\Infrastructure\Optimization;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoIntelligence;

final class DeterministicExecutionOptimizer implements ExecutionOptimizerInterface
{
    private const float LONG_DURATION_SECONDS = 1800.0;

    public function optimize(VideoIntelligence $intelligence): ExecutionOptimization
    {
        $profile = $this->resolveProfile($intelligence);
        $explanations = [];

        $speechToText = $this->speechToTextStage($intelligence, $explanations);
        $translation = $this->translationStage($intelligence, $explanations);
        $textToSpeech = $this->textToSpeechStage($intelligence, $explanations);
        $voiceClone = $this->voiceCloneStage($intelligence, $explanations);
        $lipSync = $this->lipSyncStage($intelligence, $explanations);
        $videoRender = $this->videoRenderStage($intelligence, $explanations);

        return ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            $profile,
            new OptimizationStageCollection([
                $speechToText,
                $translation,
                $textToSpeech,
                $voiceClone,
                $lipSync,
                $videoRender,
            ]),
            sprintf(
                '%s execution optimization for %s content.',
                ucfirst($profile->value),
                $intelligence->audio()->language(),
            ),
            $this->estimateImpact($profile, $intelligence),
            $explanations,
        );
    }

    private function resolveProfile(VideoIntelligence $intelligence): OptimizationProfile
    {
        if ($intelligence->audio()->confidence()->isLow()) {
            return OptimizationProfile::Quality;
        }

        if (!$intelligence->gpuAvailable()) {
            return OptimizationProfile::Speed;
        }

        if ($intelligence->estimatedVramGb() < 4.0) {
            return OptimizationProfile::LowMemory;
        }

        if ($intelligence->durationSeconds() > self::LONG_DURATION_SECONDS) {
            return OptimizationProfile::Speed;
        }

        if ($intelligence->visual()->lighting() === LightingCondition::Excellent
            || $intelligence->visual()->lighting() === LightingCondition::Good) {
            return OptimizationProfile::Quality;
        }

        return OptimizationProfile::Balanced;
    }

    /**
     * @param list<string> $explanations
     */
    private function speechToTextStage(VideoIntelligence $intelligence, array &$explanations): OptimizationStageConfiguration
    {
        $beamSize = $intelligence->audio()->confidence()->isLow() ? '5' : '3';
        $chunkSize = $intelligence->durationSeconds() > self::LONG_DURATION_SECONDS ? '60' : '30';

        if ($intelligence->audio()->confidence()->isLow()) {
            $explanations[] = 'Low STT confidence: beam size increased to 5.';
        }

        if ($intelligence->durationSeconds() > self::LONG_DURATION_SECONDS) {
            $explanations[] = 'Long video: chunk size increased to 60 seconds.';
        }

        return OptimizationStageConfiguration::create(
            OptimizationStage::SpeechToText,
            new OptimizationParameterCollection([
                OptimizationParameter::create('beamSize', $beamSize),
                OptimizationParameter::create('chunkSize', $chunkSize),
            ]),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function translationStage(VideoIntelligence $intelligence, array &$explanations): OptimizationStageConfiguration
    {
        $style = SpeechSpeed::Fast === $intelligence->audio()->speechSpeed() ? 'natural' : 'formal';
        $temperature = $intelligence->audio()->confidence()->isLow() ? '0.1' : '0.2';

        if (SpeechSpeed::Fast === $intelligence->audio()->speechSpeed()) {
            $explanations[] = 'Fast speech detected: translation style set to Natural.';
        }

        return OptimizationStageConfiguration::create(
            OptimizationStage::Translation,
            new OptimizationParameterCollection([
                OptimizationParameter::create('style', $style),
                OptimizationParameter::create('temperature', $temperature),
            ]),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function textToSpeechStage(VideoIntelligence $intelligence, array &$explanations): OptimizationStageConfiguration
    {
        $pacing = $intelligence->speech()->pauseCount() > 15 ? 'slow' : 'normal';

        if ($intelligence->speech()->pauseCount() > 15) {
            $explanations[] = 'Long pauses detected: slower TTS pacing applied.';
        }

        return OptimizationStageConfiguration::create(
            OptimizationStage::TextToSpeech,
            new OptimizationParameterCollection([
                OptimizationParameter::create('pacing', $pacing),
            ]),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function voiceCloneStage(VideoIntelligence $intelligence, array &$explanations): OptimizationStageConfiguration
    {
        $stability = $intelligence->audio()->speakerCount() >= 2 || $intelligence->audio()->backgroundMusic() === BackgroundMusic::Detected
            ? '0.85'
            : '0.70';

        if ($intelligence->audio()->speakerCount() >= 2) {
            $explanations[] = 'Multiple speakers detected: voice stability increased to 0.85.';
        }

        if ($intelligence->audio()->backgroundMusic() === BackgroundMusic::Detected) {
            $explanations[] = 'Background music detected: stronger voice clone stability recommended.';
        }

        return OptimizationStageConfiguration::create(
            OptimizationStage::VoiceClone,
            new OptimizationParameterCollection([
                OptimizationParameter::create('stability', $stability),
            ]),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function lipSyncStage(VideoIntelligence $intelligence, array &$explanations): OptimizationStageConfiguration
    {
        $strength = in_array(
            $intelligence->visual()->lipVisibility(),
            [LipVisibility::Poor, LipVisibility::Partial],
            true,
        ) ? 'low' : 'high';

        if (LipVisibility::Poor === $intelligence->visual()->lipVisibility()
            || LipVisibility::Partial === $intelligence->visual()->lipVisibility()) {
            $explanations[] = 'Poor lip visibility: lip-sync strength reduced.';
        }

        return OptimizationStageConfiguration::create(
            OptimizationStage::LipSync,
            new OptimizationParameterCollection([
                OptimizationParameter::create('strength', $strength),
            ]),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function videoRenderStage(VideoIntelligence $intelligence, array &$explanations): OptimizationStageConfiguration
    {
        $preset = in_array(
            $intelligence->visual()->lighting(),
            [LightingCondition::Excellent, LightingCondition::Good],
            true,
        ) ? 'quality' : 'standard';

        if (LightingCondition::Excellent === $intelligence->visual()->lighting()) {
            $explanations[] = 'Excellent lighting: FFmpeg quality preset selected.';
        }

        return OptimizationStageConfiguration::create(
            OptimizationStage::VideoRender,
            new OptimizationParameterCollection([
                OptimizationParameter::create('preset', $preset),
            ]),
        );
    }

    private function estimateImpact(OptimizationProfile $profile, VideoIntelligence $intelligence): int
    {
        $base = match ($profile) {
            OptimizationProfile::Quality => 5,
            OptimizationProfile::Balanced => 4,
            OptimizationProfile::Speed => 3,
            OptimizationProfile::LowMemory => 3,
        };

        if ($intelligence->audio()->confidence()->isHigh() && $base < 5) {
            return min(5, $base + 1);
        }

        return $base;
    }
}

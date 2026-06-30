<?php

declare(strict_types=1);

namespace App\Infrastructure\Quality;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityEvaluatorInterface;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\VideoRenderQuality;

final class DeterministicQualityEvaluator implements QualityEvaluatorInterface
{
    private const int LOW_STT_CONFIDENCE_THRESHOLD = 80;

    public function evaluate(
        VideoIntelligence $intelligence,
        ExecutionOptimization $optimization,
        ?FinalVideoArtifact $finalVideo,
    ): QualityReport {
        $explanations = [];
        $audio = $this->evaluateAudio($intelligence, $explanations);
        $translation = $this->evaluateTranslation($intelligence, $explanations);
        $voiceClone = $this->evaluateVoiceClone($intelligence, $explanations);
        $lipSync = $this->evaluateLipSync($intelligence, $explanations);
        $rendering = $this->evaluateRendering($intelligence, $optimization, $finalVideo, $explanations);

        $metrics = new QualityMetricCollection([
            $audio,
            $translation,
            $voiceClone,
            $lipSync,
            $rendering,
        ]);
        $overall = $metrics->averageScore();
        $recommendation = QualityReport::recommendationFor($overall);

        if (PublicationRecommendation::Ready === $recommendation) {
            $explanations[] = 'Overall quality is ready for publishing.';
        }

        return QualityReport::create(
            QualityReportId::generate(),
            $metrics,
            $overall,
            $recommendation,
            $explanations,
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function evaluateAudio(VideoIntelligence $intelligence, array &$explanations): QualityMetric
    {
        $score = QualityScore::create(98);

        if (AudioNoiseLevel::High === $intelligence->audio()->backgroundNoise()) {
            $score = $score->penalize(15);
            $explanations[] = 'Background noise detected: audio score reduced.';
        } elseif (AudioNoiseLevel::Medium === $intelligence->audio()->backgroundNoise()) {
            $score = $score->penalize(8);
            $explanations[] = 'Moderate background noise detected.';
        }

        if (BackgroundMusic::Detected === $intelligence->audio()->backgroundMusic()) {
            $score = $score->penalize(5);
            $explanations[] = 'Background music may affect audio clarity.';
        }

        return QualityMetric::create(
            QualityCategory::Audio,
            $score,
            $this->audioExplanation($intelligence),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function evaluateTranslation(VideoIntelligence $intelligence, array &$explanations): QualityMetric
    {
        $score = QualityScore::create(95);
        $confidence = $intelligence->audio()->confidence()->percentage();

        if ($confidence < self::LOW_STT_CONFIDENCE_THRESHOLD) {
            $penalty = self::LOW_STT_CONFIDENCE_THRESHOLD - $confidence;
            $score = $score->penalize((int) max(10, $penalty / 2));
            $explanations[] = 'Low STT confidence: translation score penalized.';
        }

        return QualityMetric::create(
            QualityCategory::Translation,
            $score,
            sprintf('STT confidence %d%%.', $confidence),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function evaluateVoiceClone(VideoIntelligence $intelligence, array &$explanations): QualityMetric
    {
        $score = QualityScore::create(93);
        $speakerCount = $intelligence->audio()->speakerCount();

        if ($speakerCount >= 2) {
            $score = $score->penalize(8);
            $explanations[] = 'Multiple speakers detected: voice clone complexity penalty applied.';
        }

        if (BackgroundMusic::Detected === $intelligence->audio()->backgroundMusic()) {
            $score = $score->penalize(5);
        }

        return QualityMetric::create(
            QualityCategory::VoiceClone,
            $score,
            sprintf('%d speaker(s) detected.', $speakerCount),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function evaluateLipSync(VideoIntelligence $intelligence, array &$explanations): QualityMetric
    {
        $score = QualityScore::create(95);
        $visibility = $intelligence->visual()->lipVisibility();

        if (LipVisibility::Poor === $visibility) {
            $score = $score->cap(75);
            $explanations[] = 'Poor lip visibility: lip-sync score capped.';
        } elseif (LipVisibility::Partial === $visibility) {
            $score = $score->cap(85);
            $explanations[] = 'Partial lip visibility limits lip-sync quality.';
        }

        return QualityMetric::create(
            QualityCategory::LipSync,
            $score,
            sprintf('Lip visibility: %s.', $visibility->value),
        );
    }

    /**
     * @param list<string> $explanations
     */
    private function evaluateRendering(
        VideoIntelligence $intelligence,
        ExecutionOptimization $optimization,
        ?FinalVideoArtifact $finalVideo,
        array &$explanations,
    ): QualityMetric {
        $score = QualityScore::create(90);

        if (null !== $finalVideo && VideoRenderQuality::High === $finalVideo->quality()) {
            $score = $score->bonus(10);
            $explanations[] = 'High render quality preset applied: rendering bonus.';
        } elseif (null !== $finalVideo && VideoRenderQuality::Standard === $finalVideo->quality()) {
            $score = $score->bonus(5);
        }

        $renderStage = $optimization->stages()->forStage(OptimizationStage::VideoRender);
        $preset = $renderStage?->parameters()->valueFor('preset') ?? 'standard';

        if (in_array($intelligence->visual()->lighting(), [LightingCondition::Excellent, LightingCondition::Good], true)) {
            $score = $score->bonus(5);
        }

        if ('quality' === $preset) {
            $score = $score->bonus(5);
        }

        $score = QualityScore::create(min(100, $score->value()));

        return QualityMetric::create(
            QualityCategory::Rendering,
            $score,
            null !== $finalVideo
                ? sprintf('Render quality: %s.', $finalVideo->quality()->value)
                : 'Render quality estimated from optimization profile.',
        );
    }

    private function audioExplanation(VideoIntelligence $intelligence): string
    {
        return sprintf(
            'Noise level: %s.',
            $intelligence->audio()->backgroundNoise()->value,
        );
    }
}

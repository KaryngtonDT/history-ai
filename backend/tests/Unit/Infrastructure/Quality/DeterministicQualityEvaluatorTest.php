<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Quality;

use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityReport;
use App\Domain\VideoIntelligence\AudioCharacteristics;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechCharacteristics;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoEmotion;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use App\Domain\VideoRender\FinalVideoArtifact;
use App\Domain\VideoRender\FinalVideoId;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\Video\VideoId;
use App\Infrastructure\Quality\DeterministicQualityEvaluator;
use PHPUnit\Framework\TestCase;

final class DeterministicQualityEvaluatorTest extends TestCase
{
    private DeterministicQualityEvaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new DeterministicQualityEvaluator();
    }

    public function testHighQualityVideoProducesReadyRecommendation(): void
    {
        $report = $this->evaluator->evaluate(
            $this->intelligence(confidence: 95, lipVisibility: LipVisibility::Excellent),
            $this->optimization(),
            $this->finalVideo(VideoRenderQuality::High),
        );

        self::assertGreaterThanOrEqual(90, $report->overallScore()->value());
        self::assertSame(PublicationRecommendation::Ready, $report->recommendation());
    }

    public function testLowSttConfidencePenalizesTranslation(): void
    {
        $report = $this->evaluator->evaluate(
            $this->intelligence(confidence: 65),
            $this->optimization(),
            null,
        );

        $translation = $report->metrics()->forCategory(QualityCategory::Translation);
        self::assertNotNull($translation);
        self::assertLessThan(90, $translation->score()->value());
        self::assertContains('Low STT confidence: translation score penalized.', $report->explanations());
    }

    public function testPoorLipVisibilityCapsLipSyncScore(): void
    {
        $report = $this->evaluator->evaluate(
            $this->intelligence(lipVisibility: LipVisibility::Poor),
            $this->optimization(),
            null,
        );

        $lipSync = $report->metrics()->forCategory(QualityCategory::LipSync);
        self::assertNotNull($lipSync);
        self::assertLessThanOrEqual(75, $lipSync->score()->value());
    }

    public function testBackgroundNoiseReducesAudioScore(): void
    {
        $report = $this->evaluator->evaluate(
            $this->intelligence(noise: AudioNoiseLevel::High),
            $this->optimization(),
            null,
        );

        $audio = $report->metrics()->forCategory(QualityCategory::Audio);
        self::assertNotNull($audio);
        self::assertLessThan(90, $audio->score()->value());
    }

    public function testMultipleSpeakersPenalizeVoiceClone(): void
    {
        $report = $this->evaluator->evaluate(
            $this->intelligence(speakers: 2),
            $this->optimization(),
            null,
        );

        $voiceClone = $report->metrics()->forCategory(QualityCategory::VoiceClone);
        self::assertNotNull($voiceClone);
        self::assertLessThan(93, $voiceClone->score()->value());
    }

    public function testHighRenderQualityAddsRenderingBonus(): void
    {
        $report = $this->evaluator->evaluate(
            $this->intelligence(),
            $this->optimization('quality'),
            $this->finalVideo(VideoRenderQuality::High),
        );

        $rendering = $report->metrics()->forCategory(QualityCategory::Rendering);
        self::assertNotNull($rendering);
        self::assertGreaterThanOrEqual(95, $rendering->score()->value());
    }

    public function testSevereIssuesRecommendReviewOrRegeneration(): void
    {
        $report = $this->evaluator->evaluate(
            VideoIntelligence::create(
                VideoIntelligenceId::generate(),
                120.0,
                VideoScene::Interview,
                AudioCharacteristics::create(
                    'english',
                    3,
                    AudioNoiseLevel::High,
                    BackgroundMusic::Detected,
                    SpeechSpeed::Fast,
                    SpeechConfidence::create(50),
                ),
                VisualCharacteristics::create(
                    '1280x720',
                    24.0,
                    LightingCondition::Poor,
                    LipVisibility::Poor,
                    3,
                ),
                SpeechCharacteristics::create(VideoEmotion::Neutral, 180.0, 20, true),
                VideoSpeakerCollection::empty(),
                false,
                4.0,
            ),
            $this->optimization(),
            null,
        );

        self::assertNotSame(PublicationRecommendation::Ready, $report->recommendation());
        self::assertLessThan(90, $report->overallScore()->value());
    }

    private function intelligence(
        int $confidence = 90,
        LipVisibility $lipVisibility = LipVisibility::Excellent,
        AudioNoiseLevel $noise = AudioNoiseLevel::Low,
        int $speakers = 1,
    ): VideoIntelligence {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            120.0,
            VideoScene::Interview,
            AudioCharacteristics::create(
                'english',
                $speakers,
                $noise,
                BackgroundMusic::NotDetected,
                SpeechSpeed::Normal,
                SpeechConfidence::create($confidence),
            ),
            VisualCharacteristics::create(
                '1920x1080',
                30.0,
                LightingCondition::Excellent,
                $lipVisibility,
                $speakers,
            ),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }

    private function optimization(string $preset = 'standard'): ExecutionOptimization
    {
        $stages = [];

        foreach (OptimizationStage::all() as $stage) {
            $parameters = OptimizationStage::VideoRender === $stage
                ? [OptimizationParameter::create('preset', $preset)]
                : [OptimizationParameter::create('mode', 'default')];

            $stages[] = OptimizationStageConfiguration::create(
                $stage,
                new OptimizationParameterCollection($parameters),
            );
        }

        return ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Quality,
            new OptimizationStageCollection($stages),
            'Quality optimization.',
            5,
        );
    }

    private function finalVideo(VideoRenderQuality $quality): FinalVideoArtifact
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');

        return FinalVideoArtifact::create(
            FinalVideoId::generate(),
            $videoId,
            new LipSyncArtifactId('550e8400-e29b-41d4-a716-446655440010'),
            VideoRenderProvider::FFmpeg,
            VideoRenderFormat::MP4,
            $quality,
            120.0,
            1048576,
        );
    }
}

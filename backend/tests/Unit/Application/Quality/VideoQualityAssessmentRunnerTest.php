<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Quality;

use App\Application\Quality\QualityReportJsonMapper;
use App\Application\Quality\VideoQualityAssessmentRunner;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityEvaluatorInterface;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
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
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class VideoQualityAssessmentRunnerTest extends TestCase
{
    public function testPersistsQualityReportArtifact(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);
        $intelligence = $this->intelligence();
        $optimization = ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Balanced,
            new OptimizationStageCollection([]),
            'Balanced optimization.',
            4,
        );
        $report = $this->sampleReport();

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($intelligence);

        $optimizer = $this->createMock(ExecutionOptimizerInterface::class);
        $optimizer->expects(self::never())->method('optimize');

        $evaluator = $this->createMock(QualityEvaluatorInterface::class);
        $evaluator->method('evaluate')->willReturn($report);

        $finalVideoRepository = $this->createMock(FinalVideoRepositoryInterface::class);
        $finalVideoRepository->method('findAllDetailedByVideoId')->willReturn([]);

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function ($artifact) use ($videoId): bool {
                return ArtifactType::QualityReport === $artifact->type()
                    && $artifact->contentId()->value === $videoId->value;
            }));

        $runner = new VideoQualityAssessmentRunner(
            $videoRepository,
            $intelligenceFactory,
            $optimizer,
            $evaluator,
            $finalVideoRepository,
            $artifactRepository,
            new QualityReportJsonMapper(),
        );

        $result = $runner->assess($videoId, $optimization);

        self::assertNotNull($result);
        self::assertSame(94, $result->overallScore()->value());
    }

    public function testReturnsNullWhenVideoMissing(): void
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $runner = new VideoQualityAssessmentRunner(
            $videoRepository,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(QualityEvaluatorInterface::class),
            $this->createMock(FinalVideoRepositoryInterface::class),
            $this->createMock(ArtifactRepositoryInterface::class),
            new QualityReportJsonMapper(),
        );

        self::assertNull($runner->assess(new VideoId('550e8400-e29b-41d4-a716-446655440099')));
    }

    public function testReturnsNullWhenEvaluationFails(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($this->intelligence());

        $evaluator = $this->createMock(QualityEvaluatorInterface::class);
        $evaluator->method('evaluate')->willThrowException(new \RuntimeException('evaluation failed'));

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->expects(self::never())->method('save');

        $runner = new VideoQualityAssessmentRunner(
            $videoRepository,
            $intelligenceFactory,
            $this->createMock(ExecutionOptimizerInterface::class),
            $evaluator,
            $this->createMock(FinalVideoRepositoryInterface::class),
            $artifactRepository,
            new QualityReportJsonMapper(),
        );

        self::assertNull($runner->assess($videoId));
    }

    private function intelligence(): VideoIntelligence
    {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            120.0,
            VideoScene::Interview,
            AudioCharacteristics::create('english', 1, AudioNoiseLevel::Low, BackgroundMusic::NotDetected, SpeechSpeed::Normal, SpeechConfidence::create(90)),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 1),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }

    private function sampleReport(): QualityReport
    {
        return QualityReport::create(
            QualityReportId::generate(),
            new QualityMetricCollection([
                QualityMetric::create(QualityCategory::Audio, QualityScore::create(98), 'Clean audio.'),
                QualityMetric::create(QualityCategory::Translation, QualityScore::create(95), 'Strong translation.'),
                QualityMetric::create(QualityCategory::VoiceClone, QualityScore::create(93), 'Natural voice.'),
                QualityMetric::create(QualityCategory::LipSync, QualityScore::create(89), 'Good lip sync.'),
                QualityMetric::create(QualityCategory::Rendering, QualityScore::create(100), 'High render quality.'),
            ]),
            QualityScore::create(94),
            PublicationRecommendation::Ready,
            ['Ready for publishing.'],
        );
    }
}

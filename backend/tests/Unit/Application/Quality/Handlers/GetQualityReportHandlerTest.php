<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Quality\Handlers;

use App\Application\Quality\Handlers\GetQualityReportHandler;
use App\Application\Quality\QualityReportJsonMapper;
use App\Application\Quality\Queries\GetQualityReportQuery;
use App\Application\Quality\VideoQualityAssessmentRunner;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Quality\PublicationRecommendation;
use App\Domain\Quality\QualityCategory;
use App\Domain\Quality\QualityEvaluatorInterface;
use App\Domain\Quality\QualityMetric;
use App\Domain\Quality\QualityMetricCollection;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\Quality\QualityScore;
use App\Domain\Video\Exception\InvalidVideoJobException;
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

final class GetQualityReportHandlerTest extends TestCase
{
    public function testReturnsPersistedQualityReport(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);
        $report = $this->sampleReport();
        $mapper = new QualityReportJsonMapper();
        $artifact = Artifact::create(
            ArtifactId::generate(),
            new ContentId($videoId->value),
            new ProcessingJobId($videoId->value),
            ArtifactType::QualityReport,
            ArtifactContent::fromString($mapper->toJson($report)),
        );

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([$artifact]);

        $handler = new GetQualityReportHandler(
            $videoRepository,
            $artifactRepository,
            $mapper,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(QualityEvaluatorInterface::class),
            $this->createMock(FinalVideoRepositoryInterface::class),
            $this->createAssessmentRunner(null),
        );

        $result = $handler(new GetQualityReportQuery($videoId->value));

        self::assertSame($videoId->value, $result->videoId);
        self::assertSame(94, $result->overallScore);
        self::assertSame('ready', $result->recommendation);
    }

    public function testRunsAssessmentWhenArtifactMissing(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);
        $report = $this->sampleReport();

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);
        $artifactRepository->method('save');

        $optimizer = $this->createMock(ExecutionOptimizerInterface::class);
        $optimizer->method('optimize')->willReturn(ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Balanced,
            new OptimizationStageCollection([]),
            'Balanced optimization.',
            4,
        ));

        $evaluator = $this->createMock(QualityEvaluatorInterface::class);
        $evaluator->method('evaluate')->willReturn($report);

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($this->intelligence());

        $assessmentRunner = new VideoQualityAssessmentRunner(
            $videoRepository,
            $intelligenceFactory,
            $optimizer,
            $evaluator,
            $this->createMock(FinalVideoRepositoryInterface::class),
            $artifactRepository,
            new QualityReportJsonMapper(),
        );

        $handler = new GetQualityReportHandler(
            $videoRepository,
            $artifactRepository,
            new QualityReportJsonMapper(),
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(QualityEvaluatorInterface::class),
            $this->createMock(FinalVideoRepositoryInterface::class),
            $assessmentRunner,
        );

        $result = $handler(new GetQualityReportQuery($videoId->value));

        self::assertSame(94, $result->overallScore);
    }

    public function testFallsBackToLiveEvaluationWhenAssessmentFails(): void
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

        $artifactRepository = $this->createMock(ArtifactRepositoryInterface::class);
        $artifactRepository->method('findByContentId')->willReturn([]);
        $artifactRepository->method('save')->willThrowException(new \RuntimeException('persist failed'));

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($intelligence);

        $optimizer = $this->createMock(ExecutionOptimizerInterface::class);
        $optimizer->method('optimize')->willReturn($optimization);

        $evaluator = $this->createMock(QualityEvaluatorInterface::class);
        $evaluator->method('evaluate')->willReturn($report);

        $intelligenceFactoryForRunner = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactoryForRunner->method('fromVideoJob')->willReturn($intelligence);

        $assessmentRunner = new VideoQualityAssessmentRunner(
            $videoRepository,
            $intelligenceFactoryForRunner,
            $optimizer,
            $evaluator,
            $this->createMock(FinalVideoRepositoryInterface::class),
            $artifactRepository,
            new QualityReportJsonMapper(),
        );

        $handler = new GetQualityReportHandler(
            $videoRepository,
            $artifactRepository,
            new QualityReportJsonMapper(),
            $intelligenceFactory,
            $optimizer,
            $evaluator,
            $this->createMock(FinalVideoRepositoryInterface::class),
            $assessmentRunner,
        );

        $result = $handler(new GetQualityReportQuery($videoId->value));

        self::assertSame(94, $result->overallScore);
    }

    public function testThrowsWhenVideoMissing(): void
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $handler = new GetQualityReportHandler(
            $videoRepository,
            $this->createMock(ArtifactRepositoryInterface::class),
            new QualityReportJsonMapper(),
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
            $this->createMock(QualityEvaluatorInterface::class),
            $this->createMock(FinalVideoRepositoryInterface::class),
            $this->createAssessmentRunner(null),
        );

        $this->expectException(InvalidVideoJobException::class);

        $handler(new GetQualityReportQuery('550e8400-e29b-41d4-a716-446655440099'));
    }

    private function createAssessmentRunner(?QualityReport $report): VideoQualityAssessmentRunner
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(
            VideoJob::createUploaded(
                new VideoId('550e8400-e29b-41d4-a716-446655440099'),
                'clip.mp4',
                VideoLanguage::English,
            ),
        );

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($this->intelligence());

        $evaluator = $this->createMock(QualityEvaluatorInterface::class);
        if (null !== $report) {
            $evaluator->method('evaluate')->willReturn($report);
        }

        return new VideoQualityAssessmentRunner(
            $videoRepository,
            $intelligenceFactory,
            $this->createMock(ExecutionOptimizerInterface::class),
            $evaluator,
            $this->createMock(FinalVideoRepositoryInterface::class),
            $this->createMock(ArtifactRepositoryInterface::class),
            new QualityReportJsonMapper(),
        );
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

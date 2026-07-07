<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Optimization\Handlers;

use App\Application\Optimization\Handlers\GetExecutionOptimizationHandler;
use App\Application\Optimization\Queries\GetExecutionOptimizationQuery;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Optimization\ExecutionOptimizerInterface;
use App\Domain\Optimization\OptimizationParameter;
use App\Domain\Optimization\OptimizationParameterCollection;
use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\Optimization\OptimizationStageCollection;
use App\Domain\Optimization\OptimizationStageConfiguration;
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
use PHPUnit\Framework\TestCase;

final class GetExecutionOptimizationHandlerTest extends TestCase
{
    public function testReturnsOptimizationResult(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'clip.mp4', VideoLanguage::English);
        $intelligence = $this->intelligence();
        $optimization = ExecutionOptimization::create(
            ExecutionOptimizationId::generate(),
            OptimizationProfile::Quality,
            new OptimizationStageCollection([
                OptimizationStageConfiguration::create(
                    OptimizationStage::SpeechToText,
                    new OptimizationParameterCollection([
                        OptimizationParameter::create('beamSize', '5'),
                    ]),
                ),
            ]),
            'Quality optimization.',
            5,
            ['Low STT confidence: beam size increased to 5.'],
        );

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $intelligenceFactory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $intelligenceFactory->method('fromVideoJob')->willReturn($intelligence);

        $optimizer = $this->createMock(ExecutionOptimizerInterface::class);
        $optimizer->expects(self::once())
            ->method('optimize')
            ->with($intelligence)
            ->willReturn($optimization);

        $handler = new GetExecutionOptimizationHandler($videoRepository, $intelligenceFactory, $optimizer);
        $result = $handler(new GetExecutionOptimizationQuery($videoId->value));

        self::assertSame($videoId->value, $result->videoId);
        self::assertSame('quality', $result->profile);
        self::assertSame('5', $result->stages[0]['parameters'][0]['value']);
    }

    public function testThrowsWhenVideoMissing(): void
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $handler = new GetExecutionOptimizationHandler(
            $videoRepository,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
            $this->createMock(ExecutionOptimizerInterface::class),
        );

        $this->expectException(InvalidVideoJobException::class);

        $handler(new GetExecutionOptimizationQuery('550e8400-e29b-41d4-a716-446655440099'));
    }

    private function intelligence(): VideoIntelligence
    {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            120.0,
            VideoScene::Interview,
            AudioCharacteristics::create('english', 1, AudioNoiseLevel::Low, BackgroundMusic::NotDetected, SpeechSpeed::Normal, SpeechConfidence::create(74)),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 1),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 140.0, 5, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );
    }
}

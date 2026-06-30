<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Orchestrator;

use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Orchestrator\PipelineRecommendation;
use App\Domain\Orchestrator\PipelineRecommendationId;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
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
use PHPUnit\Framework\TestCase;

final class PipelinePlannerInterfaceTest extends TestCase
{
    public function testPlannerInterfaceDefinesExpectedMethods(): void
    {
        $configuration = PipelineConfiguration::create(
            new PipelineConfigurationId('550e8400-e29b-41d4-a716-446655440010'),
            [
                PipelineStage::create(PipelineStageType::SpeechToText, 'faster_whisper'),
                PipelineStage::create(PipelineStageType::Translation, 'ollama'),
                PipelineStage::create(PipelineStageType::TextToSpeech, 'f5_tts'),
                PipelineStage::create(PipelineStageType::VoiceClone, 'openvoice'),
                PipelineStage::create(PipelineStageType::LipSync, 'latentsync'),
                PipelineStage::create(PipelineStageType::VideoRender, 'ffmpeg'),
            ],
        );

        $intelligence = VideoIntelligence::create(
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

        $recommendation = PipelineRecommendation::create(
            PipelineRecommendationId::generate(),
            ProcessingStrategy::Balanced,
            $configuration,
            'Balanced recommendation.',
            180,
            4,
            8.0,
            ['Balanced strategy selected.'],
        );

        $planner = $this->createMock(PipelinePlannerInterface::class);

        $planner
            ->expects(self::once())
            ->method('recommend')
            ->with($intelligence)
            ->willReturn($recommendation);

        $planner
            ->expects(self::once())
            ->method('recommendWithStrategy')
            ->with($intelligence, ProcessingStrategy::Quality)
            ->willReturn($recommendation);

        self::assertSame($recommendation, $planner->recommend($intelligence));
        self::assertSame(
            $recommendation,
            $planner->recommendWithStrategy($intelligence, ProcessingStrategy::Quality),
        );
    }
}

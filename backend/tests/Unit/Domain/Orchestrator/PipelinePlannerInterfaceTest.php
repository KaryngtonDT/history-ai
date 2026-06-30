<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Orchestrator;

use App\Domain\Orchestrator\PipelinePlannerInterface;
use App\Domain\Orchestrator\PipelineRecommendation;
use App\Domain\Orchestrator\PipelineRecommendationId;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Orchestrator\VideoAnalysis;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Pipeline\PipelineStage;
use App\Domain\Pipeline\PipelineStageType;
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

        $analysis = VideoAnalysis::create('english', 120.0, '1920x1080', 30.0, true, 8.0);

        $recommendation = PipelineRecommendation::create(
            PipelineRecommendationId::generate(),
            ProcessingStrategy::Balanced,
            $configuration,
            'Balanced recommendation.',
            180,
            4,
            8.0,
        );

        $planner = $this->createMock(PipelinePlannerInterface::class);

        $planner
            ->expects(self::once())
            ->method('recommend')
            ->with($analysis)
            ->willReturn($recommendation);

        $planner
            ->expects(self::once())
            ->method('recommendWithStrategy')
            ->with($analysis, ProcessingStrategy::Quality)
            ->willReturn($recommendation);

        self::assertSame($recommendation, $planner->recommend($analysis));
        self::assertSame(
            $recommendation,
            $planner->recommendWithStrategy($analysis, ProcessingStrategy::Quality),
        );
    }
}

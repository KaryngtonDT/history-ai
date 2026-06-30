<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Orchestrator;

use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Orchestrator\VideoAnalysis;
use App\Domain\Pipeline\PipelineStageType;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\Orchestrator\DeterministicPipelinePlanner;
use PHPUnit\Framework\TestCase;

final class DeterministicPipelinePlannerTest extends TestCase
{
    private DeterministicPipelinePlanner $planner;

    protected function setUp(): void
    {
        $this->planner = new DeterministicPipelinePlanner(
            (new AIEngineRegistryFactory())->create(),
        );
    }

    public function testBalancedRecommendationForEnglishVideo(): void
    {
        $analysis = VideoAnalysis::create('english', 240.0, '1920x1080', 30.0, true, 12.0);

        $recommendation = $this->planner->recommend($analysis);

        self::assertSame(ProcessingStrategy::Balanced, $recommendation->strategy());
        self::assertSame('faster_whisper', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::SpeechToText));
        self::assertSame('ollama', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::Translation));
        self::assertSame('f5_tts', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::TextToSpeech));
        self::assertSame('openvoice', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::VoiceClone));
        self::assertSame('latentsync', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::LipSync));
        self::assertSame('ffmpeg', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::VideoRender));
        self::assertGreaterThan(0, $recommendation->estimatedDurationSeconds());
        self::assertGreaterThanOrEqual(3, $recommendation->estimatedQuality());
    }

    public function testLowVramSelectsSpeedStrategy(): void
    {
        $analysis = VideoAnalysis::create('english', 120.0, '1280x720', 24.0, true, 6.0);

        $recommendation = $this->planner->recommend($analysis);

        self::assertSame(ProcessingStrategy::Speed, $recommendation->strategy());
    }

    public function testGpuUnavailableSelectsSpeedStrategy(): void
    {
        $analysis = VideoAnalysis::create('french', 90.0, '1280x720', 24.0, false, 0.0);

        $recommendation = $this->planner->recommend($analysis);

        self::assertSame(ProcessingStrategy::Speed, $recommendation->strategy());
        self::assertSame(0.0, $recommendation->estimatedVramGb());
    }

    public function testVeryLowVramSelectsLowMemoryStrategy(): void
    {
        $analysis = VideoAnalysis::create('german', 60.0, '1280x720', 24.0, true, 3.0);

        $recommendation = $this->planner->recommend($analysis);

        self::assertSame(ProcessingStrategy::LowMemory, $recommendation->strategy());
    }

    public function testRecommendWithExplicitQualityStrategy(): void
    {
        $analysis = VideoAnalysis::create('english', 180.0, '1920x1080', 30.0, true, 16.0);

        $recommendation = $this->planner->recommendWithStrategy($analysis, ProcessingStrategy::Quality);

        self::assertSame(ProcessingStrategy::Quality, $recommendation->strategy());
        self::assertSame(5, $recommendation->estimatedQuality());
    }

    public function testFallsBackToEnabledProvidersOnly(): void
    {
        $analysis = VideoAnalysis::create('english', 30.0, '640x360', 24.0, true, 12.0);

        $recommendation = $this->planner->recommendWithStrategy($analysis, ProcessingStrategy::LowMemory);

        self::assertSame('faster_whisper', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::SpeechToText));
        self::assertSame('ffmpeg', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::VideoRender));
    }
}

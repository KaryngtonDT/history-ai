<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Orchestrator;

use App\Application\Runtime\RuntimeResolverInterface;
use App\Domain\Engine\EngineCatalogCapability;
use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\Runtime\EngineExecutionPlan;
use App\Domain\Runtime\ResolvedEngine;
use App\Domain\Runtime\RuntimeResolveContext;
use App\Domain\Runtime\RuntimeResolveReason;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\Orchestrator\DeterministicPipelinePlanner;
use App\Infrastructure\Runtime\Kernel\EngineAdapterRegistry;
use App\Infrastructure\VideoIntelligence\AudioAnalyzer;
use App\Infrastructure\VideoIntelligence\CompositeVideoAnalyzer;
use App\Infrastructure\VideoIntelligence\SpeechAnalyzer;
use App\Infrastructure\VideoIntelligence\VisualAnalyzer;
use PHPUnit\Framework\TestCase;

final class DeterministicPipelinePlannerTest extends TestCase
{
    private DeterministicPipelinePlanner $planner;

    private CompositeVideoAnalyzer $analyzer;

    protected function setUp(): void
    {
        $adapterRegistry = new EngineAdapterRegistry();
        $runtimeResolver = $this->createStub(RuntimeResolverInterface::class);
        $runtimeResolver->method('resolveCapability')->willReturnCallback(
            static function (EngineCatalogCapability $capability, RuntimeResolveContext $context) use ($adapterRegistry): EngineExecutionPlan {
                $engineId = $context->preferredEngineId ?? 'faster_whisper_large_v3';
                $adapterKey = $adapterRegistry->adapterKeyForEngine($engineId);

                return new EngineExecutionPlan(
                    resolvedEngine: new ResolvedEngine(
                        engineId: $engineId,
                        displayName: $engineId,
                        capability: $capability,
                        adapterKey: $adapterKey,
                        reason: RuntimeResolveReason::PlannerContext,
                        confidence: 1.0,
                        executable: true,
                        blocked: false,
                    ),
                    planId: 'test-plan',
                    adapterKey: $adapterKey,
                );
            },
        );

        $this->planner = new DeterministicPipelinePlanner(
            (new AIEngineRegistryFactory())->create(),
            $runtimeResolver,
            $adapterRegistry,
        );
        $this->analyzer = new CompositeVideoAnalyzer(
            new AudioAnalyzer(),
            new VisualAnalyzer(),
            new SpeechAnalyzer(),
        );
    }

    public function testBalancedRecommendationForEnglishVideo(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                240.0,
                '1920x1080',
                30.0,
                30,
                str_repeat('clear english speech ', 300),
                true,
                12.0,
            ),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertSame(ProcessingStrategy::Balanced, $recommendation->strategy());
        self::assertSame('faster_whisper', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::SpeechToText));
        self::assertSame('ollama', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::Translation));
        self::assertSame('f5_tts', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::TextToSpeech));
        self::assertSame('openvoice', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::VoiceClone));
        self::assertSame('latentsync', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::LipSync));
        self::assertSame('ffmpeg', $recommendation->pipelineConfiguration()->providerFor(PipelineStageType::VideoRender));
        self::assertGreaterThan(0, $recommendation->estimatedDurationSeconds());
        self::assertGreaterThanOrEqual(3, $recommendation->estimatedQuality());
        self::assertNotEmpty($recommendation->reasons());
    }

    public function testLowVramSelectsSpeedStrategy(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('english', 120.0, '1280x720', 24.0, 20, 'sample', true, 6.0),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertSame(ProcessingStrategy::Speed, $recommendation->strategy());
    }

    public function testGpuUnavailableSelectsSpeedStrategy(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('french', 90.0, '1280x720', 24.0, 25, str_repeat('sample speech ', 120), false, 0.0),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertSame(ProcessingStrategy::Speed, $recommendation->strategy());
        self::assertSame(0.0, $recommendation->estimatedVramGb());
    }

    public function testVeryLowVramSelectsLowMemoryStrategy(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('german', 60.0, '1280x720', 24.0, 25, str_repeat('sample speech ', 120), true, 3.0),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertSame(ProcessingStrategy::LowMemory, $recommendation->strategy());
    }

    public function testLowConfidenceSelectsQualityStrategy(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('english', 180.0, '1920x1080', 30.0, 0, '', true, 12.0),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertSame(ProcessingStrategy::Quality, $recommendation->strategy());
        self::assertTrue(
            str_contains(implode(' ', $recommendation->reasons()), 'STT confidence'),
        );
    }

    public function testMultiSpeakerRecommendationIncludesOpenVoiceReason(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                300.0,
                '1920x1080',
                30.0,
                65,
                str_repeat('dialogue ', 400),
                true,
                12.0,
            ),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertGreaterThanOrEqual(2, $intelligence->audio()->speakerCount());
        self::assertTrue(str_contains(implode(' ', $recommendation->reasons()), 'speakers detected'));
    }

    public function testRecommendWithExplicitQualityStrategy(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('english', 180.0, '1920x1080', 30.0, 25, str_repeat('speech ', 200), true, 16.0),
        );

        $recommendation = $this->planner->recommendWithStrategy($intelligence, ProcessingStrategy::Quality);

        self::assertSame(ProcessingStrategy::Quality, $recommendation->strategy());
        self::assertSame(5, $recommendation->estimatedQuality());
    }

    public function testFallsBackToEnabledProvidersOnly(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('english', 120.0, '1920x1080', 30.0, 20, 'sample', true, 8.0),
        );

        $recommendation = $this->planner->recommend($intelligence);

        self::assertNotEmpty($recommendation->pipelineConfiguration()->providerFor(PipelineStageType::SpeechToText));
    }
}

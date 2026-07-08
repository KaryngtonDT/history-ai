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
use App\Domain\Review\LipSyncStrengthPreference;
use App\Domain\Review\RenderingPresetPreference;
use App\Domain\Review\TranslationStylePreference;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\VoiceStabilityPreference;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Infrastructure\AI\AIEngineRegistryFactory;
use App\Infrastructure\Orchestrator\DeterministicPipelinePlanner;
use App\Infrastructure\Runtime\Kernel\EngineAdapterRegistry;
use App\Infrastructure\VideoIntelligence\AudioAnalyzer;
use App\Infrastructure\VideoIntelligence\CompositeVideoAnalyzer;
use App\Infrastructure\VideoIntelligence\SpeechAnalyzer;
use App\Infrastructure\VideoIntelligence\VisualAnalyzer;
use PHPUnit\Framework\TestCase;

final class DeterministicPipelinePlannerPreferenceTest extends TestCase
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

    public function testNoProfileKeepsDefaultRecommendation(): void
    {
        $intelligence = $this->sampleIntelligence();

        $withoutProfile = $this->planner->recommend($intelligence);
        $withNullProfile = $this->planner->recommend($intelligence, null);

        self::assertSame($withoutProfile->strategy(), $withNullProfile->strategy());
    }

    public function testProfileOverridesStrategyTowardQuality(): void
    {
        $intelligence = $this->sampleIntelligence();
        $profile = UserPreferenceProfile::create(
            TranslationStylePreference::Natural,
            VoiceStabilityPreference::High,
            RenderingPresetPreference::Quality,
            LipSyncStrengthPreference::Moderate,
        );

        $recommendation = $this->planner->recommend($intelligence, $profile);

        self::assertSame(ProcessingStrategy::Quality, $recommendation->strategy());
        self::assertStringContainsString('preferred voice profile', $recommendation->explanation());
    }

    public function testProfileReducesLipSyncStrength(): void
    {
        $intelligence = $this->sampleIntelligence();
        $profile = UserPreferenceProfile::create(
            TranslationStylePreference::Balanced,
            VoiceStabilityPreference::Medium,
            RenderingPresetPreference::Balanced,
            LipSyncStrengthPreference::Subtle,
        );

        $recommendation = $this->planner->recommend($intelligence, $profile);

        self::assertTrue(
            str_contains(implode(' ', $recommendation->reasons()), 'Lip sync strength reduced'),
        );
    }

    public function testExplicitStrategyIgnoresPreferencesInManualMode(): void
    {
        $intelligence = $this->sampleIntelligence();
        $profile = UserPreferenceProfile::create(
            TranslationStylePreference::Natural,
            VoiceStabilityPreference::High,
            RenderingPresetPreference::Quality,
            LipSyncStrengthPreference::Subtle,
        );

        $recommendation = $this->planner->recommendWithStrategy(
            $intelligence,
            ProcessingStrategy::Speed,
        );

        self::assertSame(ProcessingStrategy::Speed, $recommendation->strategy());
        self::assertNotSame('Quality-first pipeline', substr($recommendation->explanation(), 0, 20));
    }

    private function sampleIntelligence()
    {
        return $this->analyzer->analyze(
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
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Optimization;

use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Infrastructure\Optimization\DeterministicExecutionOptimizer;
use App\Infrastructure\VideoIntelligence\AudioAnalyzer;
use App\Infrastructure\VideoIntelligence\CompositeVideoAnalyzer;
use App\Infrastructure\VideoIntelligence\SpeechAnalyzer;
use App\Infrastructure\VideoIntelligence\VisualAnalyzer;
use PHPUnit\Framework\TestCase;

final class DeterministicExecutionOptimizerTest extends TestCase
{
    private DeterministicExecutionOptimizer $optimizer;

    private CompositeVideoAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->optimizer = new DeterministicExecutionOptimizer();
        $this->analyzer = new CompositeVideoAnalyzer(
            new AudioAnalyzer(),
            new VisualAnalyzer(),
            new SpeechAnalyzer(),
        );
    }

    public function testLowConfidenceIncreasesBeamSize(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create('english', 180.0, '1920x1080', 30.0, 0, '', true, 8.0),
        );

        $optimization = $this->optimizer->optimize($intelligence);
        $stage = $optimization->stages()->forStage(OptimizationStage::SpeechToText);

        self::assertNotNull($stage);
        self::assertSame('5', $stage->parameters()->valueFor('beamSize'));
        self::assertSame(OptimizationProfile::Quality, $optimization->profile());
    }

    public function testLongVideoIncreasesChunkSize(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                2000.0,
                '1920x1080',
                30.0,
                25,
                str_repeat('speech ', 400),
                true,
                12.0,
            ),
        );

        $optimization = $this->optimizer->optimize($intelligence);
        $stage = $optimization->stages()->forStage(OptimizationStage::SpeechToText);

        self::assertSame('60', $stage?->parameters()->valueFor('chunkSize'));
    }

    public function testFastSpeechUsesNaturalTranslationStyle(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                120.0,
                '1920x1080',
                30.0,
                20,
                str_repeat('word ', 400),
                true,
                8.0,
            ),
        );

        $optimization = $this->optimizer->optimize($intelligence);
        $stage = $optimization->stages()->forStage(OptimizationStage::Translation);

        self::assertSame('natural', $stage?->parameters()->valueFor('style'));
    }

    public function testMultipleSpeakersIncreaseVoiceStability(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                300.0,
                '1920x1080',
                30.0,
                65,
                str_repeat('dialogue ', 300),
                true,
                8.0,
            ),
        );

        $optimization = $this->optimizer->optimize($intelligence);
        $stage = $optimization->stages()->forStage(OptimizationStage::VoiceClone);

        self::assertSame('0.85', $stage?->parameters()->valueFor('stability'));
    }

    public function testPoorLipVisibilityReducesLipSyncStrength(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                300.0,
                '640x480',
                18.0,
                20,
                str_repeat('speech ', 200),
                true,
                8.0,
            ),
        );

        $optimization = $this->optimizer->optimize($intelligence);
        $stage = $optimization->stages()->forStage(OptimizationStage::LipSync);

        self::assertSame('low', $stage?->parameters()->valueFor('strength'));
    }

    public function testGoodLightingUsesQualityRenderPreset(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                300.0,
                '1920x1080',
                30.0,
                25,
                str_repeat('speech ', 200),
                true,
                8.0,
            ),
        );

        $optimization = $this->optimizer->optimize($intelligence);
        $stage = $optimization->stages()->forStage(OptimizationStage::VideoRender);

        self::assertSame('quality', $stage?->parameters()->valueFor('preset'));
        self::assertNotEmpty($optimization->explanations());
    }
}

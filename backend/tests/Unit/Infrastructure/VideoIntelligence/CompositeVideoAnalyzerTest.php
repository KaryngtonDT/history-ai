<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoIntelligence;

use App\Domain\Orchestrator\ProcessingStrategy;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoScene;
use App\Infrastructure\VideoIntelligence\AudioAnalyzer;
use App\Infrastructure\VideoIntelligence\CompositeVideoAnalyzer;
use App\Infrastructure\VideoIntelligence\SpeechAnalyzer;
use App\Infrastructure\VideoIntelligence\VisualAnalyzer;
use PHPUnit\Framework\TestCase;

final class CompositeVideoAnalyzerTest extends TestCase
{
    private CompositeVideoAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new CompositeVideoAnalyzer(
            new AudioAnalyzer(),
            new VisualAnalyzer(),
            new SpeechAnalyzer(),
        );
    }

    public function testConversationSceneForMultipleSpeakers(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                900.0,
                '1920x1080',
                30.0,
                65,
                str_repeat('dialogue ', 500),
                true,
                12.0,
            ),
        );

        self::assertSame(VideoScene::Conversation, $intelligence->scene());
        self::assertGreaterThanOrEqual(2, $intelligence->speakers()->count());
    }

    public function testPresentationSceneWithSlidesHint(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                420.0,
                '1920x1080',
                30.0,
                15,
                str_repeat('slide content ', 200),
                true,
                8.0,
                true,
            ),
        );

        self::assertSame(VideoScene::Presentation, $intelligence->scene());
    }

    public function testLongDurationSelectsLectureScene(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                2000.0,
                '1280x720',
                24.0,
                12,
                str_repeat('lecture ', 800),
                true,
                8.0,
            ),
        );

        self::assertSame(VideoScene::Lecture, $intelligence->scene());
    }

    public function testLowConfidenceFromSparseTranscript(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                300.0,
                '1920x1080',
                30.0,
                0,
                '',
                true,
                8.0,
            ),
        );

        self::assertTrue($intelligence->audio()->confidence()->isLow());
    }

    public function testMusicDetectedInCompositeResult(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                180.0,
                '1920x1080',
                30.0,
                10,
                'Segment with [music] intro.',
                true,
                8.0,
            ),
        );

        self::assertSame(BackgroundMusic::Detected, $intelligence->audio()->backgroundMusic());
    }

    public function testHighConfidenceSupportsQualityStrategyHeuristic(): void
    {
        $intelligence = $this->analyzer->analyze(
            VideoAnalyzerInput::create(
                'english',
                240.0,
                '1920x1080',
                30.0,
                50,
                str_repeat('clear speech ', 400),
                true,
                12.0,
            ),
        );

        self::assertInstanceOf(SpeechConfidence::class, $intelligence->audio()->confidence());
        self::assertTrue($intelligence->audio()->confidence()->isHigh());
        self::assertSame(ProcessingStrategy::Quality, ProcessingStrategy::Quality);
    }
}

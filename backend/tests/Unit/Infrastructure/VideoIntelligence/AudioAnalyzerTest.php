<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoScene;
use App\Infrastructure\VideoIntelligence\AudioAnalyzer;
use App\Infrastructure\VideoIntelligence\CompositeVideoAnalyzer;
use App\Infrastructure\VideoIntelligence\SpeechAnalyzer;
use App\Infrastructure\VideoIntelligence\VisualAnalyzer;
use PHPUnit\Framework\TestCase;

final class AudioAnalyzerTest extends TestCase
{
    private AudioAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->analyzer = new AudioAnalyzer();
    }

    public function testDetectsMultipleSpeakersFromSegmentCount(): void
    {
        $input = VideoAnalyzerInput::create(
            'english',
            600.0,
            '1920x1080',
            30.0,
            45,
            str_repeat('word ', 2000),
            true,
            8.0,
        );

        $audio = $this->analyzer->analyze($input);

        self::assertGreaterThanOrEqual(2, $audio->speakerCount());
        self::assertSame(SpeechSpeed::Fast, $audio->speechSpeed());
    }

    public function testDetectsBackgroundMusicFromKeyword(): void
    {
        $input = VideoAnalyzerInput::create(
            'english',
            120.0,
            '1920x1080',
            30.0,
            8,
            'Intro with background music playing softly.',
            true,
            8.0,
        );

        $audio = $this->analyzer->analyze($input);

        self::assertSame(BackgroundMusic::Detected, $audio->backgroundMusic());
    }

    public function testLowSegmentCountIncreasesNoiseLevel(): void
    {
        $input = VideoAnalyzerInput::create(
            'english',
            180.0,
            '1920x1080',
            30.0,
            4,
            'short transcript',
            true,
            8.0,
        );

        $audio = $this->analyzer->analyze($input);

        self::assertSame(AudioNoiseLevel::High, $audio->backgroundNoise());
    }
}

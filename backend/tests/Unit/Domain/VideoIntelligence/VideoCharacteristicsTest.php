<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\AudioCharacteristics;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechCharacteristics;
use App\Domain\VideoIntelligence\VideoEmotion;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use PHPUnit\Framework\TestCase;

final class VideoCharacteristicsTest extends TestCase
{
    public function testAudioCharacteristicsExposeFields(): void
    {
        $audio = AudioCharacteristics::create(
            'french',
            3,
            AudioNoiseLevel::Medium,
            BackgroundMusic::NotDetected,
            SpeechSpeed::Normal,
            SpeechConfidence::create(88),
        );

        self::assertSame('french', $audio->language());
        self::assertSame(3, $audio->speakerCount());
        self::assertSame(AudioNoiseLevel::Medium, $audio->backgroundNoise());
    }

    public function testVisualCharacteristicsExposeFields(): void
    {
        $visual = VisualCharacteristics::create(
            '1280x720',
            24.0,
            LightingCondition::Average,
            LipVisibility::Partial,
            1,
        );

        self::assertSame('1280x720', $visual->resolution());
        self::assertSame(LipVisibility::Partial, $visual->lipVisibility());
    }

    public function testSpeechCharacteristicsExposeFields(): void
    {
        $speech = SpeechCharacteristics::create(
            VideoEmotion::Excited,
            180.0,
            5,
            true,
        );

        self::assertSame(VideoEmotion::Excited, $speech->dominantEmotion());
        self::assertTrue($speech->hasOverlaps());
    }

    public function testVideoSceneEnumValues(): void
    {
        self::assertSame('interview', VideoScene::Interview->value);
        self::assertSame('presentation', VideoScene::Presentation->value);
    }
}

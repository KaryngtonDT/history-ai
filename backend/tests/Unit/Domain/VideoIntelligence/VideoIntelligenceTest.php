<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\AudioCharacteristics;
use App\Domain\VideoIntelligence\AudioNoiseLevel;
use App\Domain\VideoIntelligence\BackgroundMusic;
use App\Domain\VideoIntelligence\SpeechConfidence;
use App\Domain\VideoIntelligence\SpeechSpeed;
use App\Domain\VideoIntelligence\VideoIntelligence;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeaker;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\LightingCondition;
use App\Domain\VideoIntelligence\LipVisibility;
use App\Domain\VideoIntelligence\SpeechCharacteristics;
use App\Domain\VideoIntelligence\VideoEmotion;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use PHPUnit\Framework\TestCase;

final class VideoIntelligenceTest extends TestCase
{
    public function testCreateStoresIntelligenceFields(): void
    {
        $intelligence = $this->createIntelligence();

        self::assertTrue(VideoIntelligenceId::isValid($intelligence->id()->value));
        self::assertSame(762.0, $intelligence->durationSeconds());
        self::assertSame(VideoScene::Interview, $intelligence->scene());
        self::assertSame('english', $intelligence->audio()->language());
        self::assertSame(2, $intelligence->audio()->speakerCount());
        self::assertSame('1920x1080', $intelligence->visual()->resolution());
        self::assertSame(VideoEmotion::Neutral, $intelligence->speech()->dominantEmotion());
        self::assertSame(2, $intelligence->speakers()->count());
        self::assertTrue($intelligence->gpuAvailable());
        self::assertSame(8.0, $intelligence->estimatedVramGb());
    }

    private function createIntelligence(): VideoIntelligence
    {
        return VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            762.0,
            VideoScene::Interview,
            AudioCharacteristics::create(
                'english',
                2,
                AudioNoiseLevel::Low,
                BackgroundMusic::Detected,
                SpeechSpeed::Fast,
                SpeechConfidence::create(97),
            ),
            VisualCharacteristics::create(
                '1920x1080',
                30.0,
                LightingCondition::Good,
                LipVisibility::Excellent,
                2,
            ),
            SpeechCharacteristics::create(
                VideoEmotion::Neutral,
                160.0,
                12,
                false,
            ),
            new VideoSpeakerCollection([
                VideoSpeaker::create(1, 'Speaker 1'),
                VideoSpeaker::create(2, 'Speaker 2'),
            ]),
            true,
            8.0,
        );
    }
}

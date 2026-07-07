<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\VideoIntelligence\Handlers;

use App\Application\VideoIntelligence\Handlers\GetVideoIntelligenceHandler;
use App\Application\VideoIntelligence\Queries\GetVideoIntelligenceQuery;
use App\Domain\Video\Exception\InvalidVideoJobException;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoLanguage;
use App\Domain\Video\VideoRepositoryInterface;
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
use App\Domain\VideoIntelligence\VideoIntelligenceFactoryInterface;
use App\Domain\VideoIntelligence\VideoIntelligenceId;
use App\Domain\VideoIntelligence\VideoScene;
use App\Domain\VideoIntelligence\VideoSpeakerCollection;
use App\Domain\VideoIntelligence\VisualCharacteristics;
use PHPUnit\Framework\TestCase;

final class GetVideoIntelligenceHandlerTest extends TestCase
{
    public function testReturnsIntelligenceResultForExistingVideo(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $job = VideoJob::createUploaded($videoId, 'interview.mp4', VideoLanguage::English);

        $intelligence = VideoIntelligence::create(
            VideoIntelligenceId::generate(),
            300.0,
            VideoScene::Interview,
            AudioCharacteristics::create('english', 2, AudioNoiseLevel::Low, BackgroundMusic::NotDetected, SpeechSpeed::Normal, SpeechConfidence::create(95)),
            VisualCharacteristics::create('1920x1080', 30.0, LightingCondition::Good, LipVisibility::Excellent, 2),
            SpeechCharacteristics::create(VideoEmotion::Neutral, 150.0, 8, false),
            VideoSpeakerCollection::empty(),
            true,
            8.0,
        );

        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn($job);

        $factory = $this->createMock(VideoIntelligenceFactoryInterface::class);
        $factory->expects(self::once())
            ->method('fromVideoJob')
            ->with($job)
            ->willReturn($intelligence);

        $handler = new GetVideoIntelligenceHandler($videoRepository, $factory);
        $result = $handler(new GetVideoIntelligenceQuery($videoId->value));

        self::assertSame($videoId->value, $result->videoId);
        self::assertSame('english', $result->language);
        self::assertSame(2, $result->speakerCount);
    }

    public function testThrowsWhenVideoMissing(): void
    {
        $videoRepository = $this->createMock(VideoRepositoryInterface::class);
        $videoRepository->method('findById')->willReturn(null);

        $handler = new GetVideoIntelligenceHandler(
            $videoRepository,
            $this->createMock(VideoIntelligenceFactoryInterface::class),
        );

        $this->expectException(InvalidVideoJobException::class);

        $handler(new GetVideoIntelligenceQuery('550e8400-e29b-41d4-a716-446655440099'));
    }
}

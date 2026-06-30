<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;
use App\Domain\VideoIntelligence\VideoSpeaker;
use PHPUnit\Framework\TestCase;

final class VideoSpeakerTest extends TestCase
{
    public function testCreateStoresSpeakerFields(): void
    {
        $speaker = VideoSpeaker::create(1, 'Speaker 1');

        self::assertSame(1, $speaker->index());
        self::assertSame('Speaker 1', $speaker->label());
    }

    public function testInvalidIndexThrows(): void
    {
        $this->expectException(InvalidVideoIntelligenceException::class);

        VideoSpeaker::create(0, 'Speaker 0');
    }
}

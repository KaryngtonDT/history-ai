<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\VideoIntelligence;

use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use App\Domain\VideoIntelligence\VideoEmotion;
use App\Infrastructure\VideoIntelligence\SpeechAnalyzer;
use PHPUnit\Framework\TestCase;

final class SpeechAnalyzerTest extends TestCase
{
    public function testDetectsExcitedEmotionFromTranscript(): void
    {
        $analyzer = new SpeechAnalyzer();
        $input = VideoAnalyzerInput::create(
            'english',
            120.0,
            '1920x1080',
            30.0,
            12,
            'This is amazing and we are excited to share it.',
            true,
            8.0,
        );

        $speech = $analyzer->analyze($input);

        self::assertSame(VideoEmotion::Excited, $speech->dominantEmotion());
        self::assertGreaterThan(0, $speech->pauseCount());
    }
}

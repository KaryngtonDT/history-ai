<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;
use App\Domain\VideoIntelligence\VideoAnalyzerInput;
use PHPUnit\Framework\TestCase;

final class VideoAnalyzerInputTest extends TestCase
{
    public function testCreateStoresInputFields(): void
    {
        $input = VideoAnalyzerInput::create(
            'english',
            120.0,
            '1920x1080',
            30.0,
            12,
            'sample transcript',
            true,
            8.0,
            true,
        );

        self::assertSame('english', $input->language());
        self::assertTrue($input->hasSlidesHint());
    }

    public function testInvalidDurationThrows(): void
    {
        $this->expectException(InvalidVideoIntelligenceException::class);

        VideoAnalyzerInput::create('english', -1.0, '1920x1080', 30.0, 0, '', true, 8.0);
    }
}

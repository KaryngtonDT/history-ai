<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\Exception\InvalidVideoIntelligenceException;
use App\Domain\VideoIntelligence\SpeechConfidence;
use PHPUnit\Framework\TestCase;

final class SpeechConfidenceTest extends TestCase
{
    public function testCreateStoresPercentage(): void
    {
        $confidence = SpeechConfidence::create(97);

        self::assertSame(97, $confidence->percentage());
        self::assertTrue($confidence->isHigh());
        self::assertFalse($confidence->isLow());
    }

    public function testLowConfidenceThreshold(): void
    {
        $confidence = SpeechConfidence::create(74);

        self::assertTrue($confidence->isLow());
        self::assertFalse($confidence->isHigh());
    }

    public function testInvalidPercentageThrows(): void
    {
        $this->expectException(InvalidVideoIntelligenceException::class);

        SpeechConfidence::create(101);
    }
}

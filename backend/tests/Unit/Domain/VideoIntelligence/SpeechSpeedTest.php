<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\SpeechSpeed;
use PHPUnit\Framework\TestCase;

final class SpeechSpeedTest extends TestCase
{
    public function testEnumValues(): void
    {
        self::assertSame('slow', SpeechSpeed::Slow->value);
        self::assertSame('normal', SpeechSpeed::Normal->value);
        self::assertSame('fast', SpeechSpeed::Fast->value);
    }
}

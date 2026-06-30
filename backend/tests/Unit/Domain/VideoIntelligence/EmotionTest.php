<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\VideoEmotion;
use PHPUnit\Framework\TestCase;

final class EmotionTest extends TestCase
{
    public function testEnumValues(): void
    {
        self::assertSame('neutral', VideoEmotion::Neutral->value);
        self::assertSame('happy', VideoEmotion::Happy->value);
        self::assertSame('sad', VideoEmotion::Sad->value);
        self::assertSame('angry', VideoEmotion::Angry->value);
        self::assertSame('excited', VideoEmotion::Excited->value);
    }
}

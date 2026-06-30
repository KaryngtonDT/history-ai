<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\VideoIntelligence;

use App\Domain\VideoIntelligence\AudioNoiseLevel;
use PHPUnit\Framework\TestCase;

final class NoiseLevelTest extends TestCase
{
    public function testEnumValues(): void
    {
        self::assertSame('none', AudioNoiseLevel::None->value);
        self::assertSame('low', AudioNoiseLevel::Low->value);
        self::assertSame('medium', AudioNoiseLevel::Medium->value);
        self::assertSame('high', AudioNoiseLevel::High->value);
    }
}

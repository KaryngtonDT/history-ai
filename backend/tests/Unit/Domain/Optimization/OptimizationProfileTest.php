<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Optimization;

use App\Domain\Optimization\OptimizationProfile;
use App\Domain\Optimization\OptimizationStage;
use PHPUnit\Framework\TestCase;

final class OptimizationProfileTest extends TestCase
{
    public function testEnumValues(): void
    {
        self::assertSame('balanced', OptimizationProfile::Balanced->value);
        self::assertSame('quality', OptimizationProfile::Quality->value);
        self::assertSame('speech_to_text', OptimizationStage::SpeechToText->value);
        self::assertCount(6, OptimizationStage::all());
    }
}

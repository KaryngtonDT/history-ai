<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Shadow;

use App\Domain\Shadow\ShadowChallengeLevel;
use App\Domain\Shadow\ShadowExplanationStyle;
use App\Domain\Shadow\ShadowTutorMode;
use PHPUnit\Framework\TestCase;

final class ShadowTutorModeTest extends TestCase
{
    public function testOffModeDisablesPolicy(): void
    {
        self::assertFalse(ShadowTutorMode::Off->isEnabled());
        self::assertFalse(ShadowTutorMode::Off->toPolicy()->enabled());
    }

    public function testGentleModeUsesEasyChallengeAndShortStyle(): void
    {
        $policy = ShadowTutorMode::Gentle->toPolicy();

        self::assertTrue($policy->enabled());
        self::assertSame(ShadowChallengeLevel::Easy, $policy->challengeLevel());
        self::assertSame(ShadowExplanationStyle::Short, $policy->explanationStyle());
    }

    public function testNormalModeIsMoreFrequent(): void
    {
        $gentle = ShadowTutorMode::Gentle->toPolicy();
        $normal = ShadowTutorMode::Normal->toPolicy();

        self::assertGreaterThan(
            $gentle->maxInterventionsPerMinute(),
            $normal->maxInterventionsPerMinute(),
        );
    }
}

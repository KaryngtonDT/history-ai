<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Recommendation;

use App\Domain\Recommendation\RecommendationReason;
use PHPUnit\Framework\TestCase;

final class RecommendationReasonTest extends TestCase
{
    public function testExposesExpectedStringValues(): void
    {
        self::assertSame('related', RecommendationReason::Related->value);
        self::assertSame('references', RecommendationReason::References->value);
        self::assertSame('derived_from', RecommendationReason::DerivedFrom->value);
        self::assertSame('next', RecommendationReason::Next->value);
        self::assertSame('previous', RecommendationReason::Previous->value);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

final class RecommendationWeight
{
    public const int DERIVED_FROM = 100;

    public const int REFERENCES = 80;

    public const int RELATED = 60;

    public const int NEXT = 40;

    public const int PREVIOUS = 40;

    private function __construct()
    {
    }

    public static function forReason(RecommendationReason $reason): int
    {
        return match ($reason) {
            RecommendationReason::DerivedFrom => self::DERIVED_FROM,
            RecommendationReason::References => self::REFERENCES,
            RecommendationReason::Related => self::RELATED,
            RecommendationReason::Next => self::NEXT,
            RecommendationReason::Previous => self::PREVIOUS,
        };
    }
}

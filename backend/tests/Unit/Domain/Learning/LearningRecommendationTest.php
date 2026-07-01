<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;
use App\Domain\Learning\LearningRecommendation;
use App\Domain\Learning\LearningRecommendationType;
use PHPUnit\Framework\TestCase;

final class LearningRecommendationTest extends TestCase
{
    public function testDerivesRecommendationWithExplanation(): void
    {
        $recommendation = LearningRecommendation::derive(
            LearningRecommendationType::DecreaseChallengeLevel,
            'User skipped several hard challenges',
            ['550e8400-e29b-41d4-a716-446655440010'],
        );

        self::assertSame(
            LearningRecommendationType::DecreaseChallengeLevel,
            $recommendation->type(),
        );
        self::assertSame(
            'User skipped several hard challenges',
            $recommendation->explanation(),
        );
    }

    public function testRequiresSourceInsights(): void
    {
        $this->expectException(InvalidLearningProfileException::class);

        LearningRecommendation::derive(
            LearningRecommendationType::UseLiteralTranslation,
            'No insight sources',
            [],
        );
    }
}

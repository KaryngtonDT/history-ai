<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Learning;

use App\Domain\Learning\Exception\InvalidLearningProfileException;
use App\Domain\Learning\LearningInsight;
use App\Domain\Learning\LearningInsightType;
use PHPUnit\Framework\TestCase;

final class LearningInsightTest extends TestCase
{
    public function testDerivesInsightWithSourceSignals(): void
    {
        $insight = LearningInsight::derive(
            LearningInsightType::PreferredExplanationStyle,
            'Prefers detailed explanations',
            ['550e8400-e29b-41d4-a716-446655440001'],
        );

        self::assertSame(LearningInsightType::PreferredExplanationStyle, $insight->type());
        self::assertSame(
            ['550e8400-e29b-41d4-a716-446655440001'],
            $insight->sourceSignalIds(),
        );
    }

    public function testRequiresSourceSignals(): void
    {
        $this->expectException(InvalidLearningProfileException::class);

        LearningInsight::derive(
            LearningInsightType::VocabularyGap,
            'Missing sources',
            [],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Learning;

use App\Domain\Learning\LearningPreference;
use App\Domain\Learning\LearningPreferenceCollection;
use App\Domain\Learning\LearningPreferenceKey;
use PHPUnit\Framework\TestCase;

final class LearningPreferenceTest extends TestCase
{
    public function testDefaultAdaptiveRecommendationsDisabled(): void
    {
        $preferences = LearningPreferenceCollection::default();

        self::assertFalse($preferences->adaptiveRecommendationsEnabled());
    }

    public function testUpdatesAdaptivePreference(): void
    {
        $preferences = LearningPreferenceCollection::default()
            ->withPreference(LearningPreference::adaptiveRecommendationsEnabled(true));

        self::assertTrue($preferences->adaptiveRecommendationsEnabled());
        self::assertSame(
            LearningPreferenceKey::AdaptiveRecommendationsEnabled,
            $preferences->find(LearningPreferenceKey::AdaptiveRecommendationsEnabled)?->key(),
        );
    }
}

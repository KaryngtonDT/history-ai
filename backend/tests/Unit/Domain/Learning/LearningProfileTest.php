<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Learning;

use App\Domain\Learning\LearningInsight;
use App\Domain\Learning\LearningInsightCollection;
use App\Domain\Learning\LearningInsightType;
use App\Domain\Learning\LearningPreference;
use App\Domain\Learning\LearningProfile;
use App\Domain\Learning\LearningRecommendation;
use App\Domain\Learning\LearningRecommendationCollection;
use App\Domain\Learning\LearningRecommendationType;
use App\Domain\Learning\LearningSignal;
use App\Domain\Learning\LearningSignalType;
use PHPUnit\Framework\TestCase;

final class LearningProfileTest extends TestCase
{
    public function testCreatesDefaultProfileWithAdaptiveDisabled(): void
    {
        $profile = LearningProfile::create();

        self::assertFalse($profile->adaptiveRecommendationsEnabled());
        self::assertSame(0, $profile->signals()->count());
        self::assertSame(0, $profile->insights()->count());
        self::assertSame(0, $profile->recommendations()->count());
    }

    public function testRecordsSignalsAppendOnly(): void
    {
        $profile = LearningProfile::create();
        $signal = LearningSignal::record(
            LearningSignalType::ShadowQuestionAsked,
            ['summary' => 'Asked about vocabulary'],
        );

        $updated = $profile->recordSignal($signal);

        self::assertSame(0, $profile->signals()->count());
        self::assertSame(1, $updated->signals()->count());
    }

    public function testResetClearsDerivedState(): void
    {
        $signal = LearningSignal::record(
            LearningSignalType::RepeatedVocabulary,
            ['summary' => 'compound interest'],
        );
        $insight = LearningInsight::derive(
            LearningInsightType::VocabularyGap,
            'Repeated vocabulary gap',
            [$signal->id()->value],
        );
        $recommendation = LearningRecommendation::derive(
            LearningRecommendationType::ShowVocabularyBeforePlayback,
            'Show vocabulary before playback',
            [$insight->id()->value],
        );

        $profile = LearningProfile::create()
            ->recordSignal($signal)
            ->withInsights(LearningInsightCollection::empty()->append($insight))
            ->withRecommendations(LearningRecommendationCollection::empty()->append($recommendation))
            ->enableAdaptiveRecommendations();

        $reset = $profile->reset();

        self::assertTrue($profile->adaptiveRecommendationsEnabled());
        self::assertTrue($reset->adaptiveRecommendationsEnabled());
        self::assertSame(0, $reset->signals()->count());
        self::assertSame(0, $reset->insights()->count());
        self::assertSame(0, $reset->recommendations()->count());
    }

    public function testDisableAdaptiveRecommendations(): void
    {
        $enabled = LearningProfile::create()->enableAdaptiveRecommendations();

        self::assertTrue($enabled->adaptiveRecommendationsEnabled());
        self::assertFalse($enabled->disableAdaptiveRecommendations()->adaptiveRecommendationsEnabled());
    }
}

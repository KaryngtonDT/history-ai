<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Review;

use App\Domain\Review\ReviewCategory;
use App\Domain\Review\ReviewCollection;
use App\Domain\Review\ReviewComment;
use App\Domain\Review\ReviewId;
use App\Domain\Review\Review;
use App\Domain\Review\ReviewScore;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class UserPreferenceProfileTest extends TestCase
{
    public function testExplanationLinesAreDeterministic(): void
    {
        $profile = UserPreferenceProfile::create(
            \App\Domain\Review\TranslationStylePreference::Natural,
            \App\Domain\Review\VoiceStabilityPreference::High,
            \App\Domain\Review\RenderingPresetPreference::Quality,
            \App\Domain\Review\LipSyncStrengthPreference::Subtle,
        );

        $lines = $profile->explanationLines();

        self::assertContains('Using your preferred voice profile with increased stability.', $lines);
        self::assertContains('Lip sync strength reduced according to previous feedback.', $lines);
    }

    public function testConflictingReviewsAverageToBalancedPreferences(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $collection = ReviewCollection::empty()
            ->append($this->review($videoId, 1, 5, 5, 5, 5, 5))
            ->append($this->review($videoId, 2, 1, 1, 1, 1, 1));

        $profile = UserPreferenceProfile::deriveFromReviews($collection);

        self::assertSame('balanced', $profile->translationStyle()->value);
        self::assertSame('medium', $profile->voiceStability()->value);
        self::assertSame('balanced', $profile->renderingPreset()->value);
        self::assertSame('moderate', $profile->lipSyncStrength()->value);
    }

    private function review(
        VideoId $videoId,
        int $version,
        int $overall,
        int $translation,
        int $voice,
        int $lipSync,
        int $rendering,
    ): Review {
        return Review::create(
            ReviewId::generate(),
            $videoId,
            $version,
            [
                ReviewCategory::Overall->value => ReviewScore::fromInt($overall),
                ReviewCategory::Translation->value => ReviewScore::fromInt($translation),
                ReviewCategory::VoiceClone->value => ReviewScore::fromInt($voice),
                ReviewCategory::LipSync->value => ReviewScore::fromInt($lipSync),
                ReviewCategory::Rendering->value => ReviewScore::fromInt($rendering),
            ],
            ReviewComment::empty(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Review;

use App\Domain\Review\Exception\InvalidReviewException;
use App\Domain\Review\Review;
use App\Domain\Review\ReviewCategory;
use App\Domain\Review\ReviewCollection;
use App\Domain\Review\ReviewComment;
use App\Domain\Review\ReviewId;
use App\Domain\Review\ReviewScore;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Video\VideoId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReviewTest extends TestCase
{
    public function testCreateReviewWithAllCategories(): void
    {
        $review = Review::create(
            ReviewId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            1,
            $this->sampleScores(4, 5, 3, 5, 4),
            ReviewComment::fromString('The cloned voice is slightly too robotic.'),
        );

        self::assertSame(4, $review->scoreFor(ReviewCategory::Overall)->value());
        self::assertSame(3, $review->scoreFor(ReviewCategory::VoiceClone)->value());
        self::assertSame(
            'The cloned voice is slightly too robotic.',
            $review->comment()->value(),
        );
    }

    public function testReviewRequiresAllCategoryScores(): void
    {
        $this->expectException(InvalidReviewException::class);

        Review::create(
            ReviewId::generate(),
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            1,
            [ReviewCategory::Overall->value => ReviewScore::fromInt(4)],
            ReviewComment::empty(),
        );
    }

    public function testReviewCollectionAveragesRepeatedRatings(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $collection = ReviewCollection::empty()
            ->append($this->sampleReview($videoId, 1, $this->sampleScores(5, 5, 5, 5, 5), 'First'))
            ->append($this->sampleReview($videoId, 2, $this->sampleScores(3, 3, 3, 3, 3), 'Second'));

        $averages = $collection->averageScores();

        self::assertSame(4, $averages[ReviewCategory::Overall->value]->value());
        self::assertSame(4, $averages[ReviewCategory::VoiceClone->value]->value());
    }

    public function testReviewCollectionKeepsLatestComment(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $collection = ReviewCollection::empty()
            ->append($this->sampleReview($videoId, 1, $this->sampleScores(4, 4, 4, 4, 4), 'Older comment'))
            ->append($this->sampleReview($videoId, 2, $this->sampleScores(5, 5, 5, 5, 5), 'Latest comment'));

        self::assertSame('Latest comment', $collection->latestComment()->value());
    }

    public function testPreferenceProfileGenerationFromLowVoiceScore(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $collection = ReviewCollection::empty()->append(
            $this->sampleReview(
                $videoId,
                1,
                $this->sampleScores(4, 5, 2, 2, 5),
                'Voice too robotic.',
            ),
        );

        $profile = UserPreferenceProfile::deriveFromReviews($collection);

        self::assertSame('high', $profile->voiceStability()->value);
        self::assertSame('subtle', $profile->lipSyncStrength()->value);
        self::assertSame('quality', $profile->renderingPreset()->value);
    }

    public function testPreferenceProfileGenerationFromHighScores(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440001');
        $collection = ReviewCollection::empty()->append(
            $this->sampleReview(
                $videoId,
                1,
                $this->sampleScores(5, 5, 5, 5, 5),
                'Excellent output.',
            ),
        );

        $profile = UserPreferenceProfile::deriveFromReviews($collection);

        self::assertSame('natural', $profile->translationStyle()->value);
        self::assertSame('low', $profile->voiceStability()->value);
        self::assertSame('strong', $profile->lipSyncStrength()->value);
        self::assertSame('quality', $profile->renderingPreset()->value);
    }

    /**
     * @param array<ReviewCategory, ReviewScore> $scores
     */
    private function sampleReview(
        VideoId $videoId,
        int $version,
        array $scores,
        string $comment,
    ): Review {
        return Review::create(
            ReviewId::generate(),
            $videoId,
            $version,
            $scores,
            ReviewComment::fromString($comment),
            new DateTimeImmutable(sprintf('2026-06-%02dT10:00:00+00:00', $version)),
        );
    }

    /**
     * @return array<string, ReviewScore>
     */
    private function sampleScores(
        int $overall,
        int $translation,
        int $voice,
        int $lipSync,
        int $rendering,
    ): array {
        return [
            ReviewCategory::Overall->value => ReviewScore::fromInt($overall),
            ReviewCategory::Translation->value => ReviewScore::fromInt($translation),
            ReviewCategory::VoiceClone->value => ReviewScore::fromInt($voice),
            ReviewCategory::LipSync->value => ReviewScore::fromInt($lipSync),
            ReviewCategory::Rendering->value => ReviewScore::fromInt($rendering),
        ];
    }
}

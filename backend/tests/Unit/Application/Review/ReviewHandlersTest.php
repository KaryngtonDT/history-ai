<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Review;

use App\Application\Review\BuildPreferenceProfileHandler;
use App\Application\Review\Commands\SaveReviewCommand;
use App\Application\Review\GetReviewHandler;
use App\Application\Review\Queries\GetReviewsQuery;
use App\Application\Review\SaveReviewHandler;
use App\Domain\Review\LipSyncStrengthPreference;
use App\Tests\Support\AllowAllAuthorizationGuardTrait;
use App\Domain\Review\RenderingPresetPreference;
use App\Domain\Review\Review;
use App\Domain\Review\ReviewCategory;
use App\Domain\Review\ReviewCollection;
use App\Domain\Review\ReviewComment;
use App\Domain\Review\ReviewId;
use App\Domain\Review\ReviewRepositoryInterface;
use App\Domain\Review\ReviewScore;
use App\Domain\Review\TranslationStylePreference;
use App\Domain\Review\UserPreferenceProfile;
use App\Domain\Review\UserPreferenceProfileRepositoryInterface;
use App\Domain\Review\VoiceStabilityPreference;
use App\Domain\Video\VideoId;
use PHPUnit\Framework\TestCase;

final class ReviewHandlersTest extends TestCase
{
    use AllowAllAuthorizationGuardTrait;

    public function testFirstReviewCreatesProfile(): void
    {
        $store = new InMemoryReviewStore();
        $profileHandler = $this->createProfileHandler($store);
        $handler = new SaveReviewHandler($store, $profileHandler, $this->allowAllAuthorizationGuard());

        $result = $handler(new SaveReviewCommand(
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            1,
            $this->sampleScores(4, 5, 2, 5, 5),
            'Voice too robotic.',
        ));

        self::assertSame('Voice too robotic.', $result->comment);
        self::assertCount(1, $store->findByVideoId(new VideoId('550e8400-e29b-41d4-a716-446655440001')));

        $profile = $profileHandler->getCurrent();
        self::assertNotNull($profile);
        self::assertSame('high', $profile->voiceStability);
    }

    public function testUpdateReviewAveragesRatings(): void
    {
        $store = new InMemoryReviewStore();
        $profileHandler = $this->createProfileHandler($store);
        $handler = new SaveReviewHandler($store, $profileHandler, $this->allowAllAuthorizationGuard());

        $handler(new SaveReviewCommand(
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            1,
            $this->sampleScores(5, 5, 5, 5, 5),
            'Great first pass.',
        ));
        $handler(new SaveReviewCommand(
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            2,
            $this->sampleScores(3, 3, 3, 3, 3),
            'Second pass was weaker.',
        ));

        $profile = $profileHandler->getCurrent();
        self::assertNotNull($profile);
        self::assertSame('Second pass was weaker.', $profile->latestComment);
        self::assertSame('natural', $profile->translationStyle);
        self::assertSame(2, $profile->reviewCount);
    }

    public function testGetReviewHandlerReturnsSavedReviews(): void
    {
        $store = new InMemoryReviewStore();
        $profileHandler = $this->createProfileHandler($store);
        $saveHandler = new SaveReviewHandler($store, $profileHandler, $this->allowAllAuthorizationGuard());
        $getHandler = new GetReviewHandler($store, $this->allowAllAuthorizationGuard());

        $saveHandler(new SaveReviewCommand(
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            1,
            $this->sampleScores(4, 4, 4, 4, 4),
            'Solid output.',
        ));

        $reviews = $getHandler(new GetReviewsQuery('550e8400-e29b-41d4-a716-446655440001'));

        self::assertCount(1, $reviews);
        self::assertSame('Solid output.', $reviews[0]->comment);
    }

    public function testConflictingReviewsProduceBalancedProfile(): void
    {
        $store = new InMemoryReviewStore();
        $profileHandler = $this->createProfileHandler($store);
        $handler = new SaveReviewHandler($store, $profileHandler, $this->allowAllAuthorizationGuard());

        $handler(new SaveReviewCommand(
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            1,
            $this->sampleScores(5, 5, 5, 5, 5),
            'Perfect.',
        ));
        $handler(new SaveReviewCommand(
            new VideoId('550e8400-e29b-41d4-a716-446655440002'),
            1,
            $this->sampleScores(1, 1, 1, 1, 1),
            'Poor.',
        ));

        $profile = $profileHandler->getCurrent();
        self::assertNotNull($profile);
        self::assertSame('medium', $profile->voiceStability);
        self::assertSame('moderate', $profile->lipSyncStrength);
    }

    /**
     * @return array<string, int>
     */
    private function sampleScores(
        int $overall,
        int $translation,
        int $voice,
        int $lipSync,
        int $rendering,
    ): array {
        return [
            ReviewCategory::Overall->value => $overall,
            ReviewCategory::Translation->value => $translation,
            ReviewCategory::VoiceClone->value => $voice,
            ReviewCategory::LipSync->value => $lipSync,
            ReviewCategory::Rendering->value => $rendering,
        ];
    }

    private function createProfileHandler(InMemoryReviewStore $store): BuildPreferenceProfileHandler
    {
        return new BuildPreferenceProfileHandler($store, new InMemoryPreferenceProfileStore());
    }
}

final class InMemoryReviewStore implements ReviewRepositoryInterface
{
    /** @var list<Review> */
    private array $reviews = [];

    public function append(Review $review): void
    {
        $this->reviews[] = $review;
    }

    public function findByVideoId(VideoId $videoId): array
    {
        return array_values(array_filter(
            $this->reviews,
            static fn (Review $review): bool => $review->videoId()->equals($videoId),
        ));
    }

    public function findAll(): array
    {
        return $this->reviews;
    }
}

final class InMemoryPreferenceProfileStore implements UserPreferenceProfileRepositoryInterface
{
    private ?UserPreferenceProfile $profile = null;

    public function findCurrent(): ?UserPreferenceProfile
    {
        return $this->profile;
    }

    public function save(UserPreferenceProfile $profile): void
    {
        $this->profile = $profile;
    }
}

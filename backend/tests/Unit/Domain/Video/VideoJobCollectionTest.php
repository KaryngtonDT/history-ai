<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Video;

use App\Domain\Video\VideoId;
use App\Domain\Video\VideoJob;
use App\Domain\Video\VideoJobCollection;
use App\Domain\Video\VideoLanguage;
use PHPUnit\Framework\TestCase;

final class VideoJobCollectionTest extends TestCase
{
    public function testEmptyCollectionIsAllowed(): void
    {
        $collection = VideoJobCollection::empty();

        self::assertTrue($collection->isEmpty());
        self::assertSame(0, $collection->count());
        self::assertSame([], $collection->all());
    }

    public function testAppendPreservesInsertionOrder(): void
    {
        $first = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440001'),
            'first.mp4',
            VideoLanguage::English,
        );
        $second = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440002'),
            'second.mp4',
            VideoLanguage::French,
        );
        $third = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440003'),
            'third.mp4',
            VideoLanguage::German,
        );

        $collection = VideoJobCollection::empty()
            ->append($first)
            ->append($second)
            ->append($third);

        self::assertSame(3, $collection->count());
        self::assertSame(
            [
                '550e8400-e29b-41d4-a716-446655440001',
                '550e8400-e29b-41d4-a716-446655440002',
                '550e8400-e29b-41d4-a716-446655440003',
            ],
            array_map(
                static fn (VideoJob $job): string => $job->id()->value,
                $collection->all(),
            ),
        );
    }

    public function testReturnedJobsDoNotMutateCollection(): void
    {
        $collection = VideoJobCollection::empty()->append(
            VideoJob::createUploaded(
                new VideoId('550e8400-e29b-41d4-a716-446655440001'),
                'first.mp4',
                VideoLanguage::English,
            ),
        );

        $jobs = $collection->all();
        $jobs[] = VideoJob::createUploaded(
            new VideoId('550e8400-e29b-41d4-a716-446655440002'),
            'second.mp4',
            VideoLanguage::French,
        );

        self::assertSame(1, $collection->count());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\ProjectVideo;
use App\Domain\Workspace\ProjectVideoCollection;
use PHPUnit\Framework\TestCase;

final class ProjectVideoCollectionTest extends TestCase
{
    public function testAddVideoToCollection(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $collection = ProjectVideoCollection::empty()->add(
            ProjectVideo::create($videoId, 'Demo.mp4'),
        );

        self::assertSame(1, $collection->count());
        self::assertSame($videoId->value, $collection->videoIds()[0]->value);
    }

    public function testDuplicateVideoThrows(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $video = ProjectVideo::create($videoId, 'Demo.mp4');
        $collection = ProjectVideoCollection::empty()->add($video);

        $this->expectException(InvalidProjectException::class);

        $collection->add($video);
    }

    public function testRemoveMissingVideoThrows(): void
    {
        $this->expectException(InvalidProjectException::class);

        ProjectVideoCollection::empty()->remove(
            new VideoId('550e8400-e29b-41d4-a716-446655440099'),
        );
    }
}

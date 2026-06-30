<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Workspace;

use App\Domain\Video\VideoId;
use App\Domain\Workspace\BatchJob;
use App\Domain\Workspace\BatchJobId;
use App\Domain\Workspace\BatchJobProgress;
use App\Domain\Workspace\BatchJobStatus;
use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectId;
use App\Domain\Workspace\ProjectVideo;
use App\Domain\Workspace\ProjectVideoCollection;
use PHPUnit\Framework\TestCase;

final class ProjectTest extends TestCase
{
    public function testCreateProjectWithEmptyVideos(): void
    {
        $project = Project::create(ProjectId::generate(), 'Marketing Campaign');

        self::assertTrue(ProjectId::isValid($project->id()->value));
        self::assertSame('Marketing Campaign', $project->name());
        self::assertTrue($project->videos()->isEmpty());
    }

    public function testAddAndRemoveVideo(): void
    {
        $videoId = new VideoId('550e8400-e29b-41d4-a716-446655440099');
        $project = Project::create(ProjectId::generate(), 'Campaign')
            ->addVideo(ProjectVideo::create($videoId, 'Interview.mp4'));

        self::assertSame(1, $project->videos()->count());
        self::assertTrue($project->videos()->contains($videoId));

        $updated = $project->removeVideo($videoId);
        self::assertTrue($updated->videos()->isEmpty());
    }

    public function testRenameProject(): void
    {
        $project = Project::create(ProjectId::generate(), 'Old Name')->rename('New Name');

        self::assertSame('New Name', $project->name());
    }

    public function testEmptyNameThrows(): void
    {
        $this->expectException(InvalidProjectException::class);

        Project::create(ProjectId::generate(), '   ');
    }
}

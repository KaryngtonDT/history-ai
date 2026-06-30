<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Workspace;

use App\Domain\Workspace\Exception\InvalidProjectException;
use App\Domain\Workspace\Project;
use App\Domain\Workspace\ProjectCollection;
use App\Domain\Workspace\ProjectId;
use PHPUnit\Framework\TestCase;

final class ProjectCollectionTest extends TestCase
{
    public function testAppendProject(): void
    {
        $collection = ProjectCollection::empty()->append(
            Project::create(ProjectId::generate(), 'Campaign A'),
        );

        self::assertSame(1, $collection->count());
        self::assertFalse($collection->isEmpty());
    }

    public function testProjectIdValidation(): void
    {
        $this->expectException(InvalidProjectException::class);

        new ProjectId('not-a-uuid');
    }
}

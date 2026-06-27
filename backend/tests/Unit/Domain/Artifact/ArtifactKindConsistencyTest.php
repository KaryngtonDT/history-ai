<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Artifact;

use App\Domain\Artifact\ArtifactType;
use App\Domain\Library\LibraryItemType;
use App\Domain\Processing\ProcessingJobType;
use PHPUnit\Framework\TestCase;

final class ArtifactKindConsistencyTest extends TestCase
{
    public function testTimelineIsOfficialAcrossArtifactDomains(): void
    {
        self::assertSame('timeline', ArtifactType::Timeline->value);
        self::assertSame('timeline', LibraryItemType::Timeline->value);
        self::assertSame('timeline', ProcessingJobType::Timeline->value);

        self::assertSame(ArtifactType::Timeline, ArtifactType::from('timeline'));
        self::assertSame(LibraryItemType::Timeline, LibraryItemType::from('timeline'));
        self::assertSame(ProcessingJobType::Timeline, ProcessingJobType::from('timeline'));
    }

    public function testEveryArtifactTypeMapsToLibraryItemType(): void
    {
        foreach (ArtifactType::cases() as $artifactType) {
            $libraryItemType = LibraryItemType::from($artifactType->value);

            self::assertSame(
                $artifactType->value,
                $libraryItemType->value,
                sprintf('Artifact type "%s" must map to a library item type.', $artifactType->value),
            );
        }
    }

    public function testArtifactGeneratingJobTypesIncludeTimeline(): void
    {
        $artifactGeneratingJobTypes = [
            ProcessingJobType::Summary,
            ProcessingJobType::Quiz,
            ProcessingJobType::Flashcards,
            ProcessingJobType::Podcast,
            ProcessingJobType::Timeline,
        ];

        foreach ($artifactGeneratingJobTypes as $jobType) {
            self::assertContains(
                $jobType->value,
                array_map(static fn (ArtifactType $type): string => $type->value, ArtifactType::cases()),
                sprintf('Processing job type "%s" must correspond to an artifact type.', $jobType->value),
            );
        }
    }
}

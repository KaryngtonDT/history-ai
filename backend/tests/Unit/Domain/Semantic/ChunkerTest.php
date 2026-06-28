<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Semantic;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Semantic\Chunker;
use PHPUnit\Framework\TestCase;

final class ChunkerTest extends TestCase
{
    private Chunker $chunker;

    protected function setUp(): void
    {
        $this->chunker = new Chunker();
    }

    public function testSingleHeadingProducesOneChunk(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            implode("\n", [
                '## Roman Republic',
                '',
                'The early republic expanded across Italy.',
            ]),
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(1, $collection->count());
        self::assertSame(
            "## Roman Republic\n\nThe early republic expanded across Italy.",
            $collection->chunks()[0]->text()->value(),
        );
        self::assertSame(0, $collection->chunks()[0]->position()->value());
    }

    public function testMultipleHeadingsProduceOrderedChunks(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            implode("\n", [
                '## Roman Republic',
                'Republic content',
                '## Roman Empire',
                'Empire content',
            ]),
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(2, $collection->count());
        self::assertSame(
            "## Roman Republic\nRepublic content",
            $collection->chunks()[0]->text()->value(),
        );
        self::assertSame(
            "## Roman Empire\nEmpire content",
            $collection->chunks()[1]->text()->value(),
        );
        self::assertSame(0, $collection->chunks()[0]->position()->value());
        self::assertSame(1, $collection->chunks()[1]->position()->value());
    }

    public function testNoHeadingProducesSingleChunk(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            "Plain summary text without headings.\nSecond paragraph.",
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(1, $collection->count());
        self::assertSame(
            "Plain summary text without headings.\nSecond paragraph.",
            $collection->chunks()[0]->text()->value(),
        );
    }

    public function testIgnoresEmptyChunksBetweenHeadings(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440004',
            implode("\n", [
                '   ',
                '## First',
                'Content',
                '## Third',
                'More content',
            ]),
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(2, $collection->count());
        self::assertSame("## First\nContent", $collection->chunks()[0]->text()->value());
        self::assertSame("## Third\nMore content", $collection->chunks()[1]->text()->value());
    }

    public function testTrimsWhitespaceInChunks(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440005',
            "   \n## Roman Republic\n\n  Expanded territory.  \n",
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(1, $collection->count());
        self::assertSame(
            "## Roman Republic\n\n  Expanded territory.",
            $collection->chunks()[0]->text()->value(),
        );
    }

    public function testPreservesPreambleBeforeFirstHeading(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440006',
            implode("\n", [
                '# Summary',
                'Overview paragraph',
                '## Roman Republic',
                'Republic details',
            ]),
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(2, $collection->count());
        self::assertSame(
            "# Summary\nOverview paragraph",
            $collection->chunks()[0]->text()->value(),
        );
        self::assertSame(
            "## Roman Republic\nRepublic details",
            $collection->chunks()[1]->text()->value(),
        );
    }

    public function testDoesNotSplitOnLevelThreeHeadings(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440007',
            implode("\n", [
                '## Roman Republic',
                '### Early period',
                'Details',
            ]),
        );

        $collection = $this->chunker->chunk($artifact);

        self::assertSame(1, $collection->count());
        self::assertStringContainsString('### Early period', $collection->chunks()[0]->text()->value());
    }

    public function testProducesDeterministicChunkIds(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440008',
            "## Section\nContent",
        );

        $firstRun = $this->chunker->chunk($artifact);
        $secondRun = $this->chunker->chunk($artifact);

        self::assertSame(
            $firstRun->chunks()[0]->id()->value,
            $secondRun->chunks()[0]->id()->value,
        );
    }

    private function createArtifact(string $id, string $content): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            new ContentId('7c9e6679-7425-40de-944b-e07fc1f90ae7'),
            new ProcessingJobId('6ba7b810-9dad-11d1-80b4-00c04fd430c8'),
            ArtifactType::Summary,
            ArtifactContent::fromString($content),
        );
    }
}

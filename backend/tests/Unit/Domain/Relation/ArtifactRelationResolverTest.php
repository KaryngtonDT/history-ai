<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Relation;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Relation\ArtifactRelation;
use App\Domain\Relation\ArtifactRelationCollection;
use App\Domain\Relation\ArtifactRelationResolver;
use App\Domain\Relation\ArtifactRelationType;
use PHPUnit\Framework\TestCase;

final class ArtifactRelationResolverTest extends TestCase
{
    public function testEmptyArtifactListReturnsEmptyCollection(): void
    {
        $collection = ArtifactRelationResolver::resolve([]);

        self::assertTrue($collection->isEmpty());
        self::assertSame([], $collection->relations());
    }

    public function testSummaryIsDerivedFromTranscript(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );

        $collection = ArtifactRelationResolver::resolve([$transcript, $summary]);

        self::assertTrue($this->containsRelation(
            $collection,
            $summary->id()->value,
            $transcript->id()->value,
            ArtifactRelationType::DerivedFrom,
        ));
    }

    public function testQuizReferencesSummary(): void
    {
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $quiz = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440003',
            ArtifactType::Quiz,
        );

        $collection = ArtifactRelationResolver::resolve([$summary, $quiz]);

        self::assertTrue($this->containsRelation(
            $collection,
            $quiz->id()->value,
            $summary->id()->value,
            ArtifactRelationType::References,
        ));
    }

    public function testFlashcardsReferenceSummary(): void
    {
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $flashcards = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440004',
            ArtifactType::Flashcards,
        );

        $collection = ArtifactRelationResolver::resolve([$summary, $flashcards]);

        self::assertTrue($this->containsRelation(
            $collection,
            $flashcards->id()->value,
            $summary->id()->value,
            ArtifactRelationType::References,
        ));
    }

    public function testTimelineReferencesTranscript(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $timeline = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440005',
            ArtifactType::Timeline,
        );

        $collection = ArtifactRelationResolver::resolve([$transcript, $timeline]);

        self::assertTrue($this->containsRelation(
            $collection,
            $timeline->id()->value,
            $transcript->id()->value,
            ArtifactRelationType::References,
        ));
    }

    public function testRelatedRelationsAreDeterministic(): void
    {
        $artifacts = [
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440001', ArtifactType::Transcript),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440002', ArtifactType::Summary),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440003', ArtifactType::Quiz),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440004', ArtifactType::Flashcards),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440005', ArtifactType::Timeline),
        ];

        $firstRun = ArtifactRelationResolver::resolve($artifacts);
        $secondRun = ArtifactRelationResolver::resolve(array_reverse($artifacts));

        self::assertSame(
            $this->serializeRelations($firstRun),
            $this->serializeRelations($secondRun),
        );
        self::assertTrue($this->containsRelation(
            $firstRun,
            '550e8400-e29b-41d4-a716-446655440003',
            '550e8400-e29b-41d4-a716-446655440004',
            ArtifactRelationType::Related,
        ));
    }

    public function testDoesNotCreateSelfRelations(): void
    {
        $artifact = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Summary,
        );

        $collection = ArtifactRelationResolver::resolve([$artifact]);

        self::assertTrue($collection->isEmpty());
    }

    public function testDoesNotCreateDuplicateRelations(): void
    {
        $transcript = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactType::Transcript,
        );
        $summary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactType::Summary,
        );
        $duplicateSummary = $this->createArtifact(
            '550e8400-e29b-41d4-a716-446655440006',
            ArtifactType::Summary,
        );

        $collection = ArtifactRelationResolver::resolve([$transcript, $summary, $duplicateSummary]);

        self::assertSame(
            count($collection->relations()),
            count(array_unique($this->serializeRelations($collection))),
        );
    }

    public function testResolvesFullContentArtifactGraph(): void
    {
        $artifacts = [
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440001', ArtifactType::Transcript),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440002', ArtifactType::Summary),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440003', ArtifactType::Quiz),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440004', ArtifactType::Flashcards),
            $this->createArtifact('550e8400-e29b-41d4-a716-446655440005', ArtifactType::Timeline),
        ];

        $collection = ArtifactRelationResolver::resolve($artifacts);

        self::assertTrue($this->containsRelation(
            $collection,
            '550e8400-e29b-41d4-a716-446655440002',
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactRelationType::DerivedFrom,
        ));
        self::assertTrue($this->containsRelation(
            $collection,
            '550e8400-e29b-41d4-a716-446655440003',
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactRelationType::References,
        ));
        self::assertTrue($this->containsRelation(
            $collection,
            '550e8400-e29b-41d4-a716-446655440004',
            '550e8400-e29b-41d4-a716-446655440002',
            ArtifactRelationType::References,
        ));
        self::assertTrue($this->containsRelation(
            $collection,
            '550e8400-e29b-41d4-a716-446655440005',
            '550e8400-e29b-41d4-a716-446655440001',
            ArtifactRelationType::References,
        ));
        self::assertTrue($this->containsRelation(
            $collection,
            '550e8400-e29b-41d4-a716-446655440001',
            '550e8400-e29b-41d4-a716-446655440003',
            ArtifactRelationType::Related,
        ));
    }

    private function createArtifact(string $id, ArtifactType $type): Artifact
    {
        return Artifact::create(
            new ArtifactId($id),
            ContentId::generate(),
            ProcessingJobId::generate(),
            $type,
            ArtifactContent::fromString('content for ' . $type->value),
        );
    }

    private function containsRelation(
        ArtifactRelationCollection $collection,
        string $sourceId,
        string $targetId,
        ArtifactRelationType $type,
    ): bool {
        foreach ($collection->relations() as $relation) {
            if (
                $relation->sourceArtifactId()->value === $sourceId
                && $relation->targetArtifactId()->value === $targetId
                && $relation->relationType() === $type
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function serializeRelations(ArtifactRelationCollection $collection): array
    {
        return array_map(
            static fn (ArtifactRelation $relation): string => sprintf(
                '%s->%s:%s',
                $relation->sourceArtifactId()->value,
                $relation->targetArtifactId()->value,
                $relation->relationType()->value,
            ),
            $collection->relations(),
        );
    }
}

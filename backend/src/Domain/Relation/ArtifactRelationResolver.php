<?php

declare(strict_types=1);

namespace App\Domain\Relation;

use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactType;

final class ArtifactRelationResolver
{
    /**
     * @var list<ArtifactType>
     */
    private const TYPE_ORDER = [
        ArtifactType::Transcript,
        ArtifactType::Summary,
        ArtifactType::Quiz,
        ArtifactType::Flashcards,
        ArtifactType::Timeline,
        ArtifactType::Podcast,
    ];

    /**
     * @param list<Artifact> $artifacts
     */
    public static function resolve(array $artifacts): ArtifactRelationCollection
    {
        if ([] === $artifacts) {
            return ArtifactRelationCollection::empty();
        }

        /** @var list<ArtifactRelation> $relations */
        $relations = [];
        /** @var array<string, true> $relationKeys */
        $relationKeys = [];
        /** @var array<string, true> $connectedPairs */
        $connectedPairs = [];

        $transcript = self::findFirstByType($artifacts, ArtifactType::Transcript);
        $summary = self::findFirstByType($artifacts, ArtifactType::Summary);
        $quiz = self::findFirstByType($artifacts, ArtifactType::Quiz);
        $flashcards = self::findFirstByType($artifacts, ArtifactType::Flashcards);
        $timeline = self::findFirstByType($artifacts, ArtifactType::Timeline);

        if (null !== $summary && null !== $transcript) {
            self::addRelation(
                $relations,
                $relationKeys,
                $connectedPairs,
                $summary->id(),
                $transcript->id(),
                ArtifactRelationType::DerivedFrom,
            );
        }

        if (null !== $quiz && null !== $summary) {
            self::addRelation(
                $relations,
                $relationKeys,
                $connectedPairs,
                $quiz->id(),
                $summary->id(),
                ArtifactRelationType::References,
            );
        }

        if (null !== $flashcards && null !== $summary) {
            self::addRelation(
                $relations,
                $relationKeys,
                $connectedPairs,
                $flashcards->id(),
                $summary->id(),
                ArtifactRelationType::References,
            );
        }

        if (null !== $timeline && null !== $transcript) {
            self::addRelation(
                $relations,
                $relationKeys,
                $connectedPairs,
                $timeline->id(),
                $transcript->id(),
                ArtifactRelationType::References,
            );
        }

        $sortedArtifacts = self::sortArtifacts($artifacts);
        $artifactCount = count($sortedArtifacts);

        for ($leftIndex = 0; $leftIndex < $artifactCount; ++$leftIndex) {
            for ($rightIndex = $leftIndex + 1; $rightIndex < $artifactCount; ++$rightIndex) {
                $left = $sortedArtifacts[$leftIndex];
                $right = $sortedArtifacts[$rightIndex];

                if (self::isPairConnected($connectedPairs, $left->id(), $right->id())) {
                    continue;
                }

                self::addRelation(
                    $relations,
                    $relationKeys,
                    $connectedPairs,
                    $left->id(),
                    $right->id(),
                    ArtifactRelationType::Related,
                );
            }
        }

        return new ArtifactRelationCollection($relations);
    }

    /**
     * @param list<Artifact> $artifacts
     */
    private static function findFirstByType(array $artifacts, ArtifactType $type): ?Artifact
    {
        $matches = array_values(array_filter(
            $artifacts,
            static fn (Artifact $artifact): bool => $artifact->type() === $type,
        ));

        if ([] === $matches) {
            return null;
        }

        usort(
            $matches,
            static fn (Artifact $left, Artifact $right): int => strcmp(
                $left->id()->value,
                $right->id()->value,
            ),
        );

        return $matches[0];
    }

    /**
     * @param list<Artifact> $artifacts
     *
     * @return list<Artifact>
     */
    private static function sortArtifacts(array $artifacts): array
    {
        $sorted = array_values($artifacts);

        usort(
            $sorted,
            static function (Artifact $left, Artifact $right): int {
                $typeComparison = self::typeOrderIndex($left->type()) <=> self::typeOrderIndex($right->type());

                if (0 !== $typeComparison) {
                    return $typeComparison;
                }

                return strcmp($left->id()->value, $right->id()->value);
            },
        );

        return $sorted;
    }

    private static function typeOrderIndex(ArtifactType $type): int
    {
        $index = array_search($type, self::TYPE_ORDER, true);

        return false === $index ? PHP_INT_MAX : $index;
    }

    /**
     * @param list<ArtifactRelation> $relations
     * @param array<string, true> $relationKeys
     * @param array<string, true> $connectedPairs
     */
    private static function addRelation(
        array &$relations,
        array &$relationKeys,
        array &$connectedPairs,
        ArtifactId $sourceArtifactId,
        ArtifactId $targetArtifactId,
        ArtifactRelationType $relationType,
    ): void {
        $relationKey = self::relationKey($sourceArtifactId, $targetArtifactId, $relationType);

        if (isset($relationKeys[$relationKey])) {
            return;
        }

        $relations[] = new ArtifactRelation($sourceArtifactId, $targetArtifactId, $relationType);
        $relationKeys[$relationKey] = true;
        $connectedPairs[self::pairKey($sourceArtifactId, $targetArtifactId)] = true;
    }

    /**
     * @param array<string, true> $connectedPairs
     */
    private static function isPairConnected(
        array $connectedPairs,
        ArtifactId $leftArtifactId,
        ArtifactId $rightArtifactId,
    ): bool {
        return isset($connectedPairs[self::pairKey($leftArtifactId, $rightArtifactId)]);
    }

    private static function relationKey(
        ArtifactId $sourceArtifactId,
        ArtifactId $targetArtifactId,
        ArtifactRelationType $relationType,
    ): string {
        return sprintf(
            '%s|%s|%s',
            $sourceArtifactId->value,
            $targetArtifactId->value,
            $relationType->value,
        );
    }

    private static function pairKey(ArtifactId $leftArtifactId, ArtifactId $rightArtifactId): string
    {
        $left = $leftArtifactId->value;
        $right = $rightArtifactId->value;

        if ($left <= $right) {
            return $left . '|' . $right;
        }

        return $right . '|' . $left;
    }
}

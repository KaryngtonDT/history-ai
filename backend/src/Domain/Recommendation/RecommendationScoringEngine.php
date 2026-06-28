<?php

declare(strict_types=1);

namespace App\Domain\Recommendation;

final class RecommendationScoringEngine
{
    public function score(
        RecommendedArtifactCollection $collection,
    ): ScoredRecommendationCollection {
        if ($collection->isEmpty()) {
            return ScoredRecommendationCollection::empty();
        }

        /** @var list<array{scored: ScoredRecommendation, index: int}> $scoredEntries */
        $scoredEntries = [];
        $seenArtifactIds = [];
        $index = 0;

        foreach ($collection->recommendations() as $recommendation) {
            $artifactKey = $recommendation->artifactId()->value;

            if (isset($seenArtifactIds[$artifactKey])) {
                continue;
            }

            $seenArtifactIds[$artifactKey] = true;
            $scoredEntries[] = [
                'scored' => new ScoredRecommendation(
                    recommendation: $recommendation,
                    score: new RecommendationScore(
                        RecommendationWeight::forReason($recommendation->reason()),
                    ),
                ),
                'index' => $index,
            ];
            ++$index;
        }

        usort(
            $scoredEntries,
            static function (array $left, array $right): int {
                $scoreComparison = $right['scored']->score()->value()
                    <=> $left['scored']->score()->value();

                if (0 !== $scoreComparison) {
                    return $scoreComparison;
                }

                return $left['index'] <=> $right['index'];
            },
        );

        return new ScoredRecommendationCollection(
            array_map(
                static fn (array $entry): ScoredRecommendation => $entry['scored'],
                $scoredEntries,
            ),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Presentation\Http\Response\Recommendation;

use App\Application\Recommendation\DTO\ArtifactRecommendationsResult;
use App\Application\Recommendation\DTO\RecommendedArtifactResult;

final class ArtifactRecommendationsResponse
{
    /**
     * @return array{
     *     recommendations: list<array{
     *         artifactId: string,
     *         type: string,
     *         title: string,
     *         reason: string,
     *         score: int
     *     }>
     * }
     */
    public static function fromResult(ArtifactRecommendationsResult $result): array
    {
        return [
            'recommendations' => array_map(
                static fn (RecommendedArtifactResult $recommendation): array => [
                    'artifactId' => $recommendation->artifactId,
                    'type' => $recommendation->type,
                    'title' => $recommendation->title,
                    'reason' => $recommendation->reason,
                    'score' => $recommendation->score,
                ],
                $result->recommendations,
            ),
        ];
    }
}

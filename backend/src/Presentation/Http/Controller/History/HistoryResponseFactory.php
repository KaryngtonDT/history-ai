<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\History;

use App\Application\History\DTO\ExecutionVersionResult;
use App\Application\History\DTO\ComparisonResult;

final class HistoryResponseFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function versionFromResult(ExecutionVersionResult $result): array
    {
        return [
            'versionNumber' => $result->versionNumber,
            'pipelineConfigurationId' => $result->pipelineConfigurationId,
            'optimizationId' => $result->optimizationId,
            'qualityReportId' => $result->qualityReportId,
            'renderedVideoId' => $result->renderedVideoId,
            'createdAt' => $result->createdAt,
            'optimizationProfile' => $result->optimizationProfile,
            'qualityScore' => $result->qualityScore,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function comparisonFromResult(ComparisonResult $result): array
    {
        return [
            'leftVersion' => $result->leftVersion,
            'rightVersion' => $result->rightVersion,
            'providerDifferences' => array_map(
                static fn ($difference): array => [
                    'stage' => $difference->stage,
                    'leftProvider' => $difference->leftProvider,
                    'rightProvider' => $difference->rightProvider,
                ],
                $result->providerDifferences,
            ),
            'optimizationDifference' => null === $result->optimizationDifference ? null : [
                'leftProfile' => $result->optimizationDifference->leftProfile,
                'rightProfile' => $result->optimizationDifference->rightProfile,
                'changedParameters' => $result->optimizationDifference->changedParameters,
            ],
            'qualityScoreDifference' => null === $result->qualityScoreDifference ? null : [
                'leftScore' => $result->qualityScoreDifference->leftScore,
                'rightScore' => $result->qualityScoreDifference->rightScore,
                'delta' => $result->qualityScoreDifference->delta,
            ],
        ];
    }
}

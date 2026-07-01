<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Application\Collaboration\WorkspaceAuthorizationGuard;
use App\Application\History\DTO\ComparisonResult;
use App\Application\History\DTO\OptimizationDifference;
use App\Application\History\DTO\ProviderDifference;
use App\Application\History\DTO\QualityScoreDifference;
use App\Application\History\Ports\ExecutionHistorySnapshotStoreInterface;
use App\Application\History\Queries\CompareExecutionQuery;
use App\Domain\Collaboration\WorkspaceAction;
use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\Video\VideoId;

final class CompareExecutionHandler
{
    public function __construct(
        private readonly ExecutionHistorySnapshotStoreInterface $snapshotStore,
        private readonly WorkspaceAuthorizationGuard $authorizationGuard,
    ) {
    }

    public function __invoke(CompareExecutionQuery $query): ComparisonResult
    {
        $this->authorizationGuard->assertVideoAction(
            $query->videoId,
            $query->actorUserId,
            WorkspaceAction::Compare,
        );

        $videoId = new VideoId($query->videoId);
        $left = $this->snapshotStore->findByVideoIdAndVersion($videoId, $query->leftVersion);
        $right = $this->snapshotStore->findByVideoIdAndVersion($videoId, $query->rightVersion);

        if (null === $left || null === $right) {
            throw new InvalidExecutionHistoryException('One or both execution versions were not found.');
        }

        return new ComparisonResult(
            $query->leftVersion,
            $query->rightVersion,
            $this->compareProviders($left->pipelineConfiguration, $right->pipelineConfiguration),
            $this->compareOptimization($left->optimization, $right->optimization),
            $this->compareQuality($left->qualityReport, $right->qualityReport),
        );
    }

    /**
     * @param array<string, mixed> $leftPipeline
     * @param array<string, mixed> $rightPipeline
     *
     * @return list<ProviderDifference>
     */
    private function compareProviders(array $leftPipeline, array $rightPipeline): array
    {
        $leftProviders = $this->indexedProviders($leftPipeline);
        $rightProviders = $this->indexedProviders($rightPipeline);
        $differences = [];

        foreach (array_unique([...array_keys($leftProviders), ...array_keys($rightProviders)]) as $stage) {
            $leftProvider = $leftProviders[$stage] ?? '';
            $rightProvider = $rightProviders[$stage] ?? '';

            if ($leftProvider !== $rightProvider) {
                $differences[] = new ProviderDifference($stage, $leftProvider, $rightProvider);
            }
        }

        return $differences;
    }

    /**
     * @param array<string, mixed> $pipeline
     *
     * @return array<string, string>
     */
    private function indexedProviders(array $pipeline): array
    {
        $indexed = [];
        $stages = is_array($pipeline['stages'] ?? null) ? $pipeline['stages'] : [];

        foreach ($stages as $stage) {
            if (!is_array($stage)) {
                continue;
            }

            $stageName = is_string($stage['stage'] ?? null) ? $stage['stage'] : null;
            $providerId = is_string($stage['providerId'] ?? null) ? $stage['providerId'] : null;

            if (null !== $stageName && null !== $providerId) {
                $indexed[$stageName] = $providerId;
            }
        }

        return $indexed;
    }

    /**
     * @param array<string, mixed> $leftOptimization
     * @param array<string, mixed> $rightOptimization
     */
    private function compareOptimization(array $leftOptimization, array $rightOptimization): ?OptimizationDifference
    {
        $leftProfile = is_string($leftOptimization['profile'] ?? null) ? $leftOptimization['profile'] : 'unknown';
        $rightProfile = is_string($rightOptimization['profile'] ?? null) ? $rightOptimization['profile'] : 'unknown';
        $changedParameters = $this->changedOptimizationParameters(
            is_array($leftOptimization['stages'] ?? null) ? $leftOptimization['stages'] : [],
            is_array($rightOptimization['stages'] ?? null) ? $rightOptimization['stages'] : [],
        );

        if ($leftProfile === $rightProfile && [] === $changedParameters) {
            return null;
        }

        return new OptimizationDifference($leftProfile, $rightProfile, $changedParameters);
    }

    /**
     * @param array<int, mixed> $leftStages
     * @param array<int, mixed> $rightStages
     *
     * @return list<string>
     */
    private function changedOptimizationParameters(array $leftStages, array $rightStages): array
    {
        $leftParameters = $this->flattenOptimizationParameters($leftStages);
        $rightParameters = $this->flattenOptimizationParameters($rightStages);
        $changed = [];

        foreach (array_unique([...array_keys($leftParameters), ...array_keys($rightParameters)]) as $key) {
            if (($leftParameters[$key] ?? null) !== ($rightParameters[$key] ?? null)) {
                $changed[] = $key;
            }
        }

        return $changed;
    }

    /**
     * @param array<int, mixed> $stages
     *
     * @return array<string, string>
     */
    private function flattenOptimizationParameters(array $stages): array
    {
        $parameters = [];

        foreach ($stages as $stage) {
            if (!is_array($stage)) {
                continue;
            }

            $stageName = is_string($stage['stage'] ?? null) ? $stage['stage'] : 'unknown';
            $stageParameters = is_array($stage['parameters'] ?? null) ? $stage['parameters'] : [];

            foreach ($stageParameters as $parameter) {
                if (!is_array($parameter)) {
                    continue;
                }

                $key = is_string($parameter['key'] ?? null) ? $parameter['key'] : null;
                $value = is_string($parameter['value'] ?? null) ? $parameter['value'] : null;

                if (null !== $key && null !== $value) {
                    $parameters[sprintf('%s.%s', $stageName, $key)] = $value;
                }
            }
        }

        return $parameters;
    }

    /**
     * @param array<string, mixed> $leftQuality
     * @param array<string, mixed> $rightQuality
     */
    private function compareQuality(array $leftQuality, array $rightQuality): ?QualityScoreDifference
    {
        $leftScore = is_int($leftQuality['overallScore'] ?? null) ? $leftQuality['overallScore'] : 0;
        $rightScore = is_int($rightQuality['overallScore'] ?? null) ? $rightQuality['overallScore'] : 0;

        if ($leftScore === $rightScore) {
            return null;
        }

        return new QualityScoreDifference($leftScore, $rightScore, $rightScore - $leftScore);
    }
}

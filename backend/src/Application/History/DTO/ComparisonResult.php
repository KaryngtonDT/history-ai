<?php

declare(strict_types=1);

namespace App\Application\History\DTO;

final readonly class ProviderDifference
{
    public function __construct(
        public string $stage,
        public string $leftProvider,
        public string $rightProvider,
    ) {
    }
}

final readonly class OptimizationDifference
{
    public function __construct(
        public string $leftProfile,
        public string $rightProfile,
        /** @var list<string> */
        public array $changedParameters,
    ) {
    }
}

final readonly class QualityScoreDifference
{
    public function __construct(
        public int $leftScore,
        public int $rightScore,
        public int $delta,
    ) {
    }
}

final readonly class ComparisonResult
{
    /**
     * @param list<ProviderDifference> $providerDifferences
     */
    public function __construct(
        public int $leftVersion,
        public int $rightVersion,
        public array $providerDifferences,
        public ?OptimizationDifference $optimizationDifference,
        public ?QualityScoreDifference $qualityScoreDifference,
    ) {
    }
}

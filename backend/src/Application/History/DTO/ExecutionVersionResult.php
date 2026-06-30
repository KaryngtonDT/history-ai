<?php

declare(strict_types=1);

namespace App\Application\History\DTO;

final readonly class ExecutionVersionResult
{
    public function __construct(
        public int $versionNumber,
        public string $pipelineConfigurationId,
        public string $optimizationId,
        public string $qualityReportId,
        public string $renderedVideoId,
        public string $createdAt,
        public string $optimizationProfile,
        public int $qualityScore,
    ) {
    }
}

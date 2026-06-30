<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReportId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;

final readonly class ExecutionSnapshot
{
    public function __construct(
        private PipelineConfigurationId $pipelineConfigurationId,
        private ExecutionOptimizationId $optimizationId,
        private QualityReportId $qualityReportId,
        private FinalVideoId $renderedVideoId,
    ) {
    }

    public static function create(
        PipelineConfigurationId $pipelineConfigurationId,
        ExecutionOptimizationId $optimizationId,
        QualityReportId $qualityReportId,
        FinalVideoId $renderedVideoId,
    ): self {
        return new self(
            $pipelineConfigurationId,
            $optimizationId,
            $qualityReportId,
            $renderedVideoId,
        );
    }

    public function toVersion(int $versionNumber, ?DateTimeImmutable $createdAt = null): ExecutionVersion
    {
        return ExecutionVersion::create(
            $versionNumber,
            $this->pipelineConfigurationId,
            $this->optimizationId,
            $this->qualityReportId,
            $this->renderedVideoId,
            $createdAt ?? new DateTimeImmutable(),
        );
    }

    public function pipelineConfigurationId(): PipelineConfigurationId
    {
        return $this->pipelineConfigurationId;
    }

    public function optimizationId(): ExecutionOptimizationId
    {
        return $this->optimizationId;
    }

    public function qualityReportId(): QualityReportId
    {
        return $this->qualityReportId;
    }

    public function renderedVideoId(): FinalVideoId
    {
        return $this->renderedVideoId;
    }
}

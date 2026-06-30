<?php

declare(strict_types=1);

namespace App\Domain\History;

use App\Domain\History\Exception\InvalidExecutionHistoryException;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReportId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;

final readonly class ExecutionVersion
{
    public function __construct(
        private int $versionNumber,
        private PipelineConfigurationId $pipelineConfigurationId,
        private ExecutionOptimizationId $optimizationId,
        private QualityReportId $qualityReportId,
        private FinalVideoId $renderedVideoId,
        private DateTimeImmutable $createdAt,
    ) {
        if ($versionNumber < 1) {
            throw new InvalidExecutionHistoryException('Version number must be at least 1.');
        }
    }

    public static function create(
        int $versionNumber,
        PipelineConfigurationId $pipelineConfigurationId,
        ExecutionOptimizationId $optimizationId,
        QualityReportId $qualityReportId,
        FinalVideoId $renderedVideoId,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $versionNumber,
            $pipelineConfigurationId,
            $optimizationId,
            $qualityReportId,
            $renderedVideoId,
            $createdAt,
        );
    }

    public function versionNumber(): int
    {
        return $this->versionNumber;
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

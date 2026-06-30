<?php

declare(strict_types=1);

namespace App\Application\History;

use App\Application\Pipeline\PipelineConfigurationJsonMapper;
use App\Application\Quality\QualityReportJsonMapper;
use App\Domain\History\ExecutionVersion;
use App\Domain\Optimization\ExecutionOptimization;
use App\Domain\Optimization\ExecutionOptimizationId;
use App\Domain\Pipeline\PipelineConfiguration;
use App\Domain\Pipeline\PipelineConfigurationId;
use App\Domain\Quality\QualityReport;
use App\Domain\Quality\QualityReportId;
use App\Domain\VideoRender\FinalVideoId;
use DateTimeImmutable;

final readonly class ExecutionVersionSnapshot
{
    /**
     * @param array<string, mixed> $pipelineConfiguration
     * @param array<string, mixed> $optimization
     * @param array<string, mixed> $qualityReport
     */
    public function __construct(
        public ExecutionVersion $version,
        public array $pipelineConfiguration,
        public array $optimization,
        public array $qualityReport,
    ) {
    }

    public static function fromCompletedRender(
        ExecutionVersion $version,
        PipelineConfiguration $pipelineConfiguration,
        ExecutionOptimization $optimization,
        QualityReport $qualityReport,
        PipelineConfigurationJsonMapper $pipelineMapper,
        ExecutionOptimizationSnapshotMapper $optimizationMapper,
        QualityReportJsonMapper $qualityMapper,
    ): self {
        return new self(
            $version,
            $pipelineMapper->toArray($pipelineConfiguration),
            $optimizationMapper->toArray($optimization),
            $qualityMapper->toArray($qualityReport),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            ExecutionVersion::create(
                (int) ($payload['versionNumber'] ?? 0),
                new PipelineConfigurationId((string) ($payload['pipelineConfigurationId'] ?? '')),
                new ExecutionOptimizationId((string) ($payload['optimizationId'] ?? '')),
                new QualityReportId((string) ($payload['qualityReportId'] ?? '')),
                new FinalVideoId((string) ($payload['renderedVideoId'] ?? '')),
                new DateTimeImmutable((string) ($payload['createdAt'] ?? 'now')),
            ),
            is_array($payload['pipelineConfiguration'] ?? null) ? $payload['pipelineConfiguration'] : [],
            is_array($payload['optimization'] ?? null) ? $payload['optimization'] : [],
            is_array($payload['qualityReport'] ?? null) ? $payload['qualityReport'] : [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'versionNumber' => $this->version->versionNumber(),
            'pipelineConfigurationId' => $this->version->pipelineConfigurationId()->value,
            'optimizationId' => $this->version->optimizationId()->value,
            'qualityReportId' => $this->version->qualityReportId()->value,
            'renderedVideoId' => $this->version->renderedVideoId()->value,
            'createdAt' => $this->version->createdAt()->format(DateTimeImmutable::ATOM),
            'pipelineConfiguration' => $this->pipelineConfiguration,
            'optimization' => $this->optimization,
            'qualityReport' => $this->qualityReport,
        ];
    }
}

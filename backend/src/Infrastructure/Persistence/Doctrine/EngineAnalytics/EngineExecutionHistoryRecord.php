<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\EngineAnalytics;

use App\Domain\EngineAnalytics\EngineExecutionHistory;
use App\Domain\EngineAnalytics\EngineExecutionHistoryId;
use App\Domain\EngineAnalytics\EngineExecutionStatus;
use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJobId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'engine_execution_history')]
class EngineExecutionHistoryRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'pipeline_job_id', type: Types::GUID)]
    private string $pipelineJobId;

    #[ORM\Column(name: 'source_id', type: Types::GUID)]
    private string $sourceId;

    #[ORM\Column(length: 64, enumType: PipelineStageType::class)]
    private PipelineStageType $stage;

    #[ORM\Column(name: 'engine_id', length: 128)]
    private string $engineId;

    #[ORM\Column(name: 'engine_version', length: 64, nullable: true)]
    private ?string $engineVersion = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(name: 'hardware_profile', length: 64)]
    private string $hardwareProfile;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $language = null;

    #[ORM\Column(name: 'media_duration_seconds', type: Types::INTEGER, nullable: true)]
    private ?int $mediaDurationSeconds = null;

    #[ORM\Column(name: 'input_size_bytes', type: Types::INTEGER, nullable: true)]
    private ?int $inputSizeBytes = null;

    #[ORM\Column(name: 'output_size_bytes', type: Types::INTEGER, nullable: true)]
    private ?int $outputSizeBytes = null;

    #[ORM\Column(name: 'estimated_duration_seconds', type: Types::INTEGER)]
    private int $estimatedDurationSeconds;

    #[ORM\Column(name: 'actual_duration_seconds', type: Types::INTEGER)]
    private int $actualDurationSeconds;

    #[ORM\Column(name: 'estimation_error_seconds', type: Types::INTEGER)]
    private int $estimationErrorSeconds;

    #[ORM\Column(name: 'estimation_accuracy_percent', type: Types::FLOAT)]
    private float $estimationAccuracyPercent;

    #[ORM\Column(name: 'started_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $startedAt;

    #[ORM\Column(name: 'completed_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $completedAt;

    #[ORM\Column(length: 32, enumType: EngineExecutionStatus::class)]
    private EngineExecutionStatus $status;

    #[ORM\Column(name: 'benchmark_score', type: Types::FLOAT, nullable: true)]
    private ?float $benchmarkScore = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public static function fromDomain(EngineExecutionHistory $execution): self
    {
        $record = new self();
        $record->id = $execution->executionId()->value;
        $record->syncFromDomain($execution);

        return $record;
    }

    public function syncFromDomain(EngineExecutionHistory $execution): void
    {
        $this->pipelineJobId = $execution->pipelineJobId()->value;
        $this->sourceId = $execution->sourceId();
        $this->stage = $execution->stage();
        $this->engineId = $execution->engineId();
        $this->engineVersion = $execution->engineVersion();
        $this->provider = $execution->provider();
        $this->hardwareProfile = $execution->hardwareProfile();
        $this->model = $execution->model();
        $this->language = $execution->language();
        $this->mediaDurationSeconds = $execution->mediaDurationSeconds();
        $this->inputSizeBytes = $execution->inputSizeBytes();
        $this->outputSizeBytes = $execution->outputSizeBytes();
        $this->estimatedDurationSeconds = $execution->estimatedDurationSeconds();
        $this->actualDurationSeconds = $execution->actualDurationSeconds();
        $this->estimationErrorSeconds = $execution->estimationErrorSeconds();
        $this->estimationAccuracyPercent = $execution->estimationAccuracyPercent();
        $this->startedAt = $execution->startedAt();
        $this->completedAt = $execution->completedAt();
        $this->status = $execution->status();
        $this->benchmarkScore = $execution->benchmarkScore();
        $this->notes = $execution->notes();
    }

    public function toDomain(): EngineExecutionHistory
    {
        return new EngineExecutionHistory(
            new EngineExecutionHistoryId($this->id),
            new PipelineJobId($this->pipelineJobId),
            $this->sourceId,
            $this->stage,
            $this->engineId,
            $this->engineVersion,
            $this->provider,
            $this->hardwareProfile,
            $this->model,
            $this->language,
            $this->mediaDurationSeconds,
            $this->inputSizeBytes,
            $this->outputSizeBytes,
            $this->estimatedDurationSeconds,
            $this->actualDurationSeconds,
            $this->estimationErrorSeconds,
            $this->estimationAccuracyPercent,
            $this->startedAt,
            $this->completedAt,
            $this->status,
            $this->benchmarkScore,
            $this->notes,
        );
    }
}

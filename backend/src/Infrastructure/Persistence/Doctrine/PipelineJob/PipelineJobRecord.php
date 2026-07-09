<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\PipelineJob;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineJobId;
use App\Domain\PipelineJob\PipelineJobStatus;
use App\Domain\PipelineJob\PipelineSourceType;
use App\Domain\PipelineJob\TranscriptSource;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pipeline_job')]
class PipelineJobRecord
{
    #[ORM\Id]
    #[ORM\Column(type: Types::GUID)]
    private string $id;

    #[ORM\Column(name: 'source_id', type: Types::GUID)]
    private string $sourceId;

    #[ORM\Column(name: 'video_id', type: Types::GUID, nullable: true)]
    private ?string $videoId = null;

    #[ORM\Column(name: 'audio_id', type: Types::GUID, nullable: true)]
    private ?string $audioId = null;

    #[ORM\Column(name: 'content_id', type: Types::GUID, nullable: true)]
    private ?string $contentId = null;

    #[ORM\Column(name: 'source_type', length: 32, enumType: PipelineSourceType::class)]
    private PipelineSourceType $sourceType;

    #[ORM\Column(length: 64, enumType: PipelineStageType::class)]
    private PipelineStageType $stage;

    #[ORM\Column(length: 64, enumType: PipelineJobStatus::class)]
    private PipelineJobStatus $status;

    #[ORM\Column(name: 'progress_percent', type: Types::INTEGER)]
    private int $progressPercent = 0;

    #[ORM\Column(name: 'current_step', length: 255, nullable: true)]
    private ?string $currentStep = null;

    #[ORM\Column(name: 'current_engine', length: 128, nullable: true)]
    private ?string $currentEngine = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(name: 'started_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'completed_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'estimated_duration_seconds', type: Types::INTEGER, nullable: true)]
    private ?int $estimatedDurationSeconds = null;

    #[ORM\Column(name: 'estimated_remaining_seconds', type: Types::INTEGER, nullable: true)]
    private ?int $estimatedRemainingSeconds = null;

    #[ORM\Column(name: 'elapsed_seconds', type: Types::INTEGER, nullable: true)]
    private ?int $elapsedSeconds = null;

    #[ORM\Column(name: 'cancellation_reason', type: Types::TEXT, nullable: true)]
    private ?string $cancellationReason = null;

    #[ORM\Column(name: 'failure_reason', type: Types::TEXT, nullable: true)]
    private ?string $failureReason = null;

    #[ORM\Column(name: 'result_artifact_id', type: Types::GUID, nullable: true)]
    private ?string $resultArtifactId = null;

    #[ORM\Column(name: 'depends_on_stage', length: 64, nullable: true)]
    private ?string $dependsOnStage = null;

    /** @var list<string> */
    #[ORM\Column(name: 'invalidates_stages', type: Types::JSON)]
    private array $invalidatesStages = [];

    /** @var list<string> */
    #[ORM\Column(name: 'stale_artifact_ids', type: Types::JSON)]
    private array $staleArtifactIds = [];

    #[ORM\Column(name: 'transcript_source', length: 64, nullable: true)]
    private ?string $transcriptSource = null;

    #[ORM\Column(name: 'user_choice_required', type: Types::BOOLEAN)]
    private bool $userChoiceRequired = false;

    /** @var list<string> */
    #[ORM\Column(name: 'user_choice_options', type: Types::JSON)]
    private array $userChoiceOptions = [];

    /** @var array<string, mixed>|null */
    #[ORM\Column(name: 'progress_detail', type: Types::JSON, nullable: true)]
    private ?array $progressDetail = null;

    private function __construct()
    {
    }

    public static function fromDomain(PipelineJob $job): self
    {
        $record = new self();
        $record->id = $job->jobId()->value;
        $record->syncFromDomain($job);

        return $record;
    }

    public function syncFromDomain(PipelineJob $job): void
    {
        $this->sourceId = $job->sourceId();
        $this->videoId = $job->videoId();
        $this->audioId = $job->audioId();
        $this->contentId = $job->contentId();
        $this->sourceType = $job->sourceType();
        $this->stage = $job->stage();
        $this->status = $job->status();
        $this->progressPercent = $job->progressPercent();
        $this->currentStep = $job->currentStep();
        $this->currentEngine = $job->currentEngine();
        $this->provider = $job->provider();
        $this->createdAt = $job->createdAt();
        $this->updatedAt = $job->updatedAt();
        $this->startedAt = $job->startedAt();
        $this->completedAt = $job->completedAt();
        $this->estimatedDurationSeconds = $job->estimatedDurationSeconds();
        $this->estimatedRemainingSeconds = $job->estimatedRemainingSeconds();
        $this->elapsedSeconds = $job->elapsedSeconds();
        $this->cancellationReason = $job->cancellationReason();
        $this->failureReason = $job->failureReason();
        $this->resultArtifactId = $job->resultArtifactId();
        $this->dependsOnStage = $job->dependsOnStage()?->value;
        $this->invalidatesStages = $job->invalidatesStages();
        $this->staleArtifactIds = $job->staleArtifactIds();
        $this->transcriptSource = $job->transcriptSource()?->value;
        $this->userChoiceRequired = $job->userChoiceRequired();
        $this->userChoiceOptions = $job->userChoiceOptions();
        $detail = $job->progressDetail();
        $this->progressDetail = [] === $detail ? null : $detail;
    }

    public function toDomain(): PipelineJob
    {
        $dependsOn = null !== $this->dependsOnStage
            ? PipelineStageType::tryFrom($this->dependsOnStage)
            : null;
        $transcriptSource = null !== $this->transcriptSource
            ? TranscriptSource::tryFrom($this->transcriptSource)
            : null;

        return PipelineJob::reconstitute(
            new PipelineJobId($this->id),
            $this->sourceId,
            $this->videoId,
            $this->audioId,
            $this->contentId,
            $this->sourceType,
            $this->stage,
            $this->status,
            $this->progressPercent,
            $this->currentStep,
            $this->currentEngine,
            $this->provider,
            $this->createdAt,
            $this->updatedAt,
            $this->startedAt,
            $this->completedAt,
            $this->estimatedDurationSeconds,
            $this->estimatedRemainingSeconds,
            $this->elapsedSeconds,
            $this->cancellationReason,
            $this->failureReason,
            $this->resultArtifactId,
            $dependsOn,
            $this->invalidatesStages,
            $this->staleArtifactIds,
            $transcriptSource,
            $this->userChoiceRequired,
            $this->userChoiceOptions,
            $this->progressDetail ?? [],
        );
    }
}

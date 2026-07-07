<?php

declare(strict_types=1);

namespace App\Application\Pipeline\Orchestration;

use App\Domain\Pipeline\PipelineStageType;
use App\Domain\PipelineJob\PipelineJob;
use App\Domain\PipelineJob\PipelineNotification;
use App\Domain\PipelineJob\PipelineNotificationId;
use App\Domain\PipelineJob\PipelineNotificationRepositoryInterface;
use App\Domain\PipelineJob\PipelineNotificationType;

final class PipelineNotificationService
{
    public function __construct(
        private readonly PipelineNotificationRepositoryInterface $notificationRepository,
    ) {
    }

    public function notifyOriginalYoutubeTranscriptFound(string $sourceId): void
    {
        $this->persist(
            $sourceId,
            PipelineNotificationType::OriginalYoutubeTranscriptFound,
            'Original YouTube transcript found',
            'An original-language YouTube transcript is available. Choose whether to use it or run the local transcription engine.',
            PipelineStageType::SpeechToText,
            '/video/'.$sourceId,
        );
    }

    public function notifyUserChoiceRequired(string $sourceId): void
    {
        $this->persist(
            $sourceId,
            PipelineNotificationType::UserChoiceRequired,
            'Transcript choice required',
            'Please choose between the YouTube transcript and local speech-to-text.',
            PipelineStageType::SpeechToText,
            '/video/'.$sourceId,
        );
    }

    public function notifyLocalSttStarted(string $sourceId, string $estimateMessage): void
    {
        $this->persist(
            $sourceId,
            PipelineNotificationType::LocalSttStarted,
            'Local transcription started',
            $estimateMessage,
            PipelineStageType::SpeechToText,
            '/video/'.$sourceId,
        );
    }

    public function notifyStageCompleted(PipelineJob $job): void
    {
        $this->persist(
            $job->sourceId(),
            PipelineNotificationType::StageCompleted,
            sprintf('%s completed', $this->stageLabel($job->stage())),
            sprintf('Stage %s completed. Review the result and continue when ready.', $this->stageLabel($job->stage())),
            $job->stage(),
            '/video/'.$job->sourceId(),
        );
        $this->persist(
            $job->sourceId(),
            PipelineNotificationType::UserConfirmationRequired,
            'Continue pipeline?',
            sprintf('Continue to the next stage after %s?', $this->stageLabel($job->stage())),
            $job->stage(),
            '/video/'.$job->sourceId(),
        );
    }

    public function notifyStageFailed(PipelineJob $job): void
    {
        $this->persist(
            $job->sourceId(),
            PipelineNotificationType::StageFailed,
            sprintf('%s failed', $this->stageLabel($job->stage())),
            $job->failureReason() ?? 'Stage failed.',
            $job->stage(),
            '/video/'.$job->sourceId(),
        );
    }

    public function notifyStageCancelled(PipelineJob $job, string $reason): void
    {
        $this->persist(
            $job->sourceId(),
            PipelineNotificationType::StageCancelled,
            sprintf('%s cancelled', $this->stageLabel($job->stage())),
            $reason,
            $job->stage(),
            '/video/'.$job->sourceId(),
        );
    }

    /**
     * @param list<string> $invalidatedStages
     */
    public function notifyStagesInvalidated(string $sourceId, PipelineStageType $restartedStage, array $invalidatedStages): void
    {
        $this->persist(
            $sourceId,
            PipelineNotificationType::StagesInvalidated,
            'Later stages invalidated',
            sprintf(
                'Restarting %s invalidated: %s',
                $this->stageLabel($restartedStage),
                implode(', ', $invalidatedStages),
            ),
            $restartedStage,
            '/video/'.$sourceId,
        );
    }

    private function persist(
        string $sourceId,
        PipelineNotificationType $type,
        string $title,
        string $message,
        ?PipelineStageType $stage = null,
        ?string $actionUrl = null,
    ): void {
        $this->notificationRepository->save(PipelineNotification::create(
            PipelineNotificationId::generate(),
            $sourceId,
            $type,
            $title,
            $message,
            $stage,
            $actionUrl,
        ));
    }

    private function stageLabel(PipelineStageType $stage): string
    {
        return match ($stage) {
            PipelineStageType::SpeechToText => 'Transcription',
            PipelineStageType::Translation => 'Translation',
            PipelineStageType::TextToSpeech => 'Audio',
            PipelineStageType::VoiceClone => 'Voice clone',
            PipelineStageType::LipSync => 'Lip sync',
            PipelineStageType::VideoRender => 'Render',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

enum PipelineNotificationType: string
{
    case OriginalYoutubeTranscriptFound = 'original_youtube_transcript_found';
    case UserChoiceRequired = 'user_choice_required';
    case LocalSttStarted = 'local_stt_started';
    case StageCompleted = 'stage_completed';
    case StageFailed = 'stage_failed';
    case StageCancelled = 'stage_cancelled';
    case UserConfirmationRequired = 'user_confirmation_required';
    case StagesInvalidated = 'stages_invalidated';
}

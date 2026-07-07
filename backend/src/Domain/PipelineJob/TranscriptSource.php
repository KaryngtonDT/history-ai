<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

enum TranscriptSource: string
{
    case YoutubeOriginalCaptions = 'youtube_original_captions';
    case YoutubeOriginalAutoCaptions = 'youtube_original_auto_captions';
    case FasterWhisper = 'faster_whisper';
    case Deterministic = 'deterministic';
}

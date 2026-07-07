<?php

declare(strict_types=1);

namespace App\Domain\PipelineJob;

enum TranscriptUserChoice: string
{
    case YoutubeTranscript = 'youtube_transcript';
    case LocalEngine = 'local_engine';
    case None = 'none';
}

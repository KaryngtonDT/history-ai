<?php

declare(strict_types=1);

namespace App\Domain\Pipeline;

/**
 * Platform default provider identifiers when no explicit configuration exists.
 */
final class PipelineDefaultProviders
{
    public const string SPEECH_TO_TEXT = 'faster_whisper';

    public const string TRANSLATION = 'ollama';

    public const string TEXT_TO_SPEECH = 'f5_tts';

    public const string VOICE_CLONE = 'openvoice';

    public const string LIP_SYNC = 'latentsync';

    public const string VIDEO_RENDER = 'ffmpeg';
}

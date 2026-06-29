<?php

declare(strict_types=1);

namespace App\Domain\Speech;

use App\Domain\Video\VideoJob;

interface SpeechToTextProviderInterface
{
    public function transcribe(VideoJob $video): Transcript;
}

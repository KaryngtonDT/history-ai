<?php

declare(strict_types=1);

namespace App\Domain\TTS;

use App\Domain\Translation\Translation;

interface TextToSpeechProviderInterface
{
    public function synthesize(Translation $translation, Voice $voice): AudioArtifact;
}

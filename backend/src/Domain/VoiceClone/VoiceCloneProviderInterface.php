<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

use App\Domain\Translation\Translation;
use App\Domain\TTS\AudioArtifact;

interface VoiceCloneProviderInterface
{
    public function cloneVoice(AudioArtifact $source, Translation $translation): VoiceCloneArtifact;
}

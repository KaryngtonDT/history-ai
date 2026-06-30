<?php

declare(strict_types=1);

namespace App\Domain\LipSync;

use App\Domain\Video\VideoJob;
use App\Domain\VoiceClone\VoiceCloneArtifact;

interface LipSyncProviderInterface
{
    public function synchronize(VideoJob $video, VoiceCloneArtifact $voiceClone): LipSyncArtifact;
}

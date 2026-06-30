<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Domain\LipSync\LipSyncVideo;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Video\VideoJob;
use App\Domain\VoiceClone\VoiceCloneArtifact;

final class MockLipSyncProvider implements LipSyncProviderInterface
{
    public function synchronize(VideoJob $video, VoiceCloneArtifact $voiceClone): LipSyncArtifact
    {
        $duration = max(1.0, $voiceClone->profile()->duration());

        return LipSyncArtifact::create(
            LipSyncArtifactId::generate(),
            $video->id(),
            $voiceClone->clonedAudioId(),
            LipSyncProvider::Mock,
            LipSyncVideo::create(
                LipSyncVideoId::generate(),
                '/tmp/mock-lip-sync.mp4',
                $duration,
            ),
        );
    }
}

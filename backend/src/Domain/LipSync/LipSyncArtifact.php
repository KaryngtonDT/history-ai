<?php

declare(strict_types=1);

namespace App\Domain\LipSync;

use App\Domain\TTS\AudioId;
use App\Domain\Video\VideoId;

final readonly class LipSyncArtifact
{
    public function __construct(
        private LipSyncArtifactId $artifactId,
        private VideoId $sourceVideoId,
        private AudioId $clonedAudioId,
        private LipSyncProvider $provider,
        private LipSyncVideo $video,
    ) {
    }

    public static function create(
        LipSyncArtifactId $artifactId,
        VideoId $sourceVideoId,
        AudioId $clonedAudioId,
        LipSyncProvider $provider,
        LipSyncVideo $video,
    ): self {
        return new self($artifactId, $sourceVideoId, $clonedAudioId, $provider, $video);
    }

    public function artifactId(): LipSyncArtifactId
    {
        return $this->artifactId;
    }

    public function sourceVideoId(): VideoId
    {
        return $this->sourceVideoId;
    }

    public function provider(): LipSyncProvider
    {
        return $this->provider;
    }

    public function video(): LipSyncVideo
    {
        return $this->video;
    }

    public function audio(): AudioId
    {
        return $this->clonedAudioId;
    }

    public function synchronizedVideoId(): LipSyncVideoId
    {
        return $this->video->synchronizedVideoId();
    }
}

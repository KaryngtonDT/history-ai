<?php

declare(strict_types=1);

namespace App\Infrastructure\LipSync;

use App\Domain\LipSync\LipSyncArtifact;
use App\Domain\LipSync\LipSyncArtifactId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncProviderInterface;
use App\Domain\LipSync\LipSyncVideoId;
use App\Domain\Video\VideoJob;
use App\Domain\VoiceClone\VoiceCloneArtifact;
use App\Infrastructure\LipSync\Exception\LatentSyncProviderException;

final class LatentSyncProvider implements LipSyncProviderInterface
{
    public function __construct(
        private readonly LatentSyncProcessRunner $processRunner,
        private readonly LipSyncMapper $lipSyncMapper,
        private readonly string $binary,
        private readonly string $model,
        private readonly string $basePath,
        private readonly string $outputDirectory,
    ) {
    }

    public function synchronize(VideoJob $video, VoiceCloneArtifact $voiceClone): LipSyncArtifact
    {
        $videoPath = $video->storagePath();

        if (null === $videoPath || '' === trim($videoPath)) {
            throw new LatentSyncProviderException('Source video storage path is required for lip sync.');
        }

        if ('' === trim($voiceClone->storagePath())) {
            throw new LatentSyncProviderException('Cloned audio storage path cannot be empty.');
        }

        $synchronizedVideoId = LipSyncVideoId::generate();
        $artifactId = LipSyncArtifactId::generate();
        $outputPath = rtrim($this->outputDirectory, '/\\').DIRECTORY_SEPARATOR.$synchronizedVideoId->value.'.mp4';

        $command = [
            $this->binary,
            '--video',
            $videoPath,
            '--audio',
            $voiceClone->storagePath(),
            '--model',
            $this->model,
            '--base-path',
            $this->basePath,
            '--output',
            $outputPath,
            '--audio-duration',
            (string) $voiceClone->profile()->duration(),
        ];

        $output = $this->processRunner->run($command);

        return $this->lipSyncMapper->toArtifact(
            $output,
            $video->id(),
            $voiceClone->clonedAudioId(),
            LipSyncProvider::LatentSync,
            $artifactId,
            $synchronizedVideoId,
            $outputPath,
        );
    }
}

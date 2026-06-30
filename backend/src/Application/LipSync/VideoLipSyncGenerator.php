<?php

declare(strict_types=1);

namespace App\Application\LipSync;

use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\LipSync\LipSyncProvider;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;

class VideoLipSyncGenerator
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly VoiceCloneRepositoryInterface $voiceCloneRepository,
        private readonly LipSyncRepositoryInterface $lipSyncRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly LipSyncJsonMapper $lipSyncJsonMapper,
    ) {
    }

    /**
     * @param list<TranslationLanguage> $targetLanguages
     */
    public function generate(
        VideoId $videoId,
        ?LipSyncProvider $provider = null,
        array $targetLanguages = [],
    ): void {
        $video = $this->videoRepository->findById($videoId);

        if (null === $video) {
            return;
        }

        $storagePath = $video->storagePath();

        if (null === $storagePath || '' === trim($storagePath)) {
            return;
        }

        $voiceClones = $this->voiceCloneRepository->findAllByVideoId($videoId);

        if ([] === $voiceClones) {
            return;
        }

        $lipSyncProvider = $this->aiProviderResolver->resolveLipSync($provider);

        foreach ($voiceClones as $voiceClone) {
            if ([] !== $targetLanguages && !in_array($voiceClone->targetLanguage(), $targetLanguages, true)) {
                continue;
            }

            $synced = $lipSyncProvider->synchronize($video, $voiceClone);

            $this->lipSyncRepository->save($videoId, $voiceClone->targetLanguage(), $synced);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::LipSync,
                ArtifactContent::fromString(
                    $this->lipSyncJsonMapper->toJson($synced, $voiceClone->targetLanguage()),
                ),
            );
            $this->artifactRepository->save($artifact);
        }
    }
}

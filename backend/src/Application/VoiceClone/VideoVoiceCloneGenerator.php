<?php

declare(strict_types=1);

namespace App\Application\VoiceClone;

use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VoiceClone\VoiceCloneProvider;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;
use App\Domain\VoiceClone\VoiceCloneReferenceContextInterface;

class VideoVoiceCloneGenerator
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly TranslationRepositoryInterface $translationRepository,
        private readonly AudioRepositoryInterface $audioRepository,
        private readonly VoiceCloneRepositoryInterface $voiceCloneRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly VoiceCloneReferenceContextInterface $processingContext,
        private readonly VoiceCloneJsonMapper $voiceCloneJsonMapper,
    ) {
    }

    public function generate(
        VideoId $videoId,
        ?VoiceCloneProvider $provider = null,
        array $targetLanguages = [],
    ): void {
        $video = $this->videoRepository->findById($videoId);

        if (null === $video) {
            return;
        }

        $referencePath = $video->storagePath();

        if (null === $referencePath || '' === trim($referencePath)) {
            return;
        }

        $translations = $this->translationRepository->findAllByVideoId($videoId);

        if ([] === $translations) {
            return;
        }

        $voiceCloneProvider = $this->aiProviderResolver->resolveVoiceClone($provider);

        foreach ($translations as $translation) {
            if ([] !== $targetLanguages && !in_array($translation->targetLanguage(), $targetLanguages, true)) {
                continue;
            }

            $sourceAudio = $this->audioRepository->findByVideoIdAndLanguage(
                $videoId,
                $translation->targetLanguage(),
            );

            if (null === $sourceAudio) {
                continue;
            }

            $cloned = $this->processingContext->withReference(
                $referencePath,
                static fn () => $voiceCloneProvider->cloneVoice($sourceAudio, $translation),
            );

            $this->voiceCloneRepository->save($videoId, $cloned);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::VoiceClone,
                ArtifactContent::fromString($this->voiceCloneJsonMapper->toJson($cloned)),
            );
            $this->artifactRepository->save($artifact);
        }
    }
}

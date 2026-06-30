<?php

declare(strict_types=1);

namespace App\Application\TTS;

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
use App\Domain\TTS\TextToSpeechProvider;
use App\Domain\Video\VideoId;

class VideoAudioGenerator
{
    public function __construct(
        private readonly TranslationRepositoryInterface $translationRepository,
        private readonly AudioRepositoryInterface $audioRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly DefaultVoiceSelector $voiceSelector,
        private readonly AudioJsonMapper $audioJsonMapper,
    ) {
    }

    public function generate(
        VideoId $videoId,
        ?TextToSpeechProvider $provider = null,
        ?string $voiceId = null,
        array $targetLanguages = [],
    ): void {
        $translations = $this->translationRepository->findAllByVideoId($videoId);

        if ([] === $translations) {
            return;
        }

        $textToSpeechProvider = $this->aiProviderResolver->resolveTextToSpeech($provider);

        foreach ($translations as $translation) {
            if ([] !== $targetLanguages && !in_array($translation->targetLanguage(), $targetLanguages, true)) {
                continue;
            }

            $voice = $this->voiceSelector->resolve($translation->targetLanguage(), $voiceId);
            $audio = $textToSpeechProvider->synthesize($translation, $voice);
            $this->audioRepository->save($videoId, $audio);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::Audio,
                ArtifactContent::fromString($this->audioJsonMapper->toJson($audio)),
            );
            $this->artifactRepository->save($artifact);
        }
    }
}

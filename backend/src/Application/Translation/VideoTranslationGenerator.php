<?php

declare(strict_types=1);

namespace App\Application\Translation;

use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Translation\TranslationProvider;
use App\Domain\Translation\TranslationRepositoryInterface;
use App\Domain\Video\VideoId;

class VideoTranslationGenerator
{
    public function __construct(
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly TranslationRepositoryInterface $translationRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly TranslationJsonMapper $translationJsonMapper,
    ) {
    }

    /**
     * @param list<TranslationLanguage> $targetLanguages
     */
    public function generate(
        VideoId $videoId,
        array $targetLanguages,
        ?TranslationProvider $provider = null,
    ): void {
        $transcript = $this->transcriptRepository->findByVideoId($videoId);

        if (null === $transcript) {
            return;
        }

        $translationProvider = $this->aiProviderResolver->resolveTranslation($provider);

        foreach ($targetLanguages as $targetLanguage) {
            if ($targetLanguage === TranslationLanguage::Unknown) {
                continue;
            }

            $translation = $translationProvider->translate($transcript, $targetLanguage);
            $this->translationRepository->save($videoId, $translation);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::Translation,
                ArtifactContent::fromString($this->translationJsonMapper->toJson($translation)),
            );
            $this->artifactRepository->save($artifact);
        }
    }
}

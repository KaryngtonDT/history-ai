<?php

declare(strict_types=1);

namespace App\Application\Translation;

use App\Application\Pipeline\Orchestration\PipelineStageProgressReporter;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\PipelineJob\PipelineJobId;
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
        ?PipelineStageProgressReporter $progress = null,
    ): void {
        $transcript = $this->transcriptRepository->findByVideoId($videoId);

        if (null === $transcript) {
            return;
        }

        $languages = array_values(array_filter(
            $targetLanguages,
            static fn (TranslationLanguage $language): bool => TranslationLanguage::Unknown !== $language,
        ));

        if ([] === $languages) {
            return;
        }

        $progress?->checkpoint('preparing', 5, 'preparing');
        $progress?->checkpoint('loading', 12, 'loading_model');

        $translationProvider = $this->aiProviderResolver->resolveTranslation($provider);
        $total = count($languages);

        $progress?->checkpoint('processing', 15, 'translating', [
            'totalLanguages' => $total,
            'currentLanguage' => 0,
        ]);

        foreach ($languages as $index => $targetLanguage) {
            $progress?->heartbeat();

            $segment = $index + 1;
            $startRatio = $total > 0 ? $index / $total : 0.0;
            $startPercent = 15 + (int) round(75 * $startRatio);

            $progress?->checkpoint('processing', max(15, $startPercent), 'translating', [
                'currentLanguage' => $index,
                'totalLanguages' => $total,
            ]);

            $translation = $translationProvider->translate($transcript, $targetLanguage);
            $this->translationRepository->save($videoId, $translation);

            $endRatio = $total > 0 ? $segment / $total : 1.0;
            $endPercent = 15 + (int) round(75 * $endRatio);

            $progress?->checkpoint('processing', $endPercent, 'translating', [
                'currentLanguage' => $segment,
                'totalLanguages' => $total,
            ]);

            $progress?->checkpoint('saving', min(98, $endPercent + 2), 'saving_translation', [
                'currentLanguage' => $segment,
                'totalLanguages' => $total,
            ]);

            $processingJobId = null !== $progress
                ? new ProcessingJobId($progress->jobId()->value)
                : new ProcessingJobId(PipelineJobId::generate()->value);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                $processingJobId,
                ArtifactType::Translation,
                ArtifactContent::fromString($this->translationJsonMapper->toJson($translation)),
            );
            $this->artifactRepository->save($artifact);

            $progress?->heartbeat();
        }

        $progress?->checkpoint('completed', 99, 'completed');
    }
}

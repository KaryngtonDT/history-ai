<?php

declare(strict_types=1);

namespace App\Application\VideoRender;

use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;
use App\Domain\VideoRender\VideoRenderFormat;
use App\Domain\VideoRender\VideoRenderProvider;
use App\Domain\VideoRender\VideoRenderQuality;

class VideoFinalRenderGenerator
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly LipSyncRepositoryInterface $lipSyncRepository,
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly VideoRenderJsonMapper $videoRenderJsonMapper,
        private readonly string $outputDirectory,
        private readonly VideoRenderFormat $defaultFormat,
        private readonly VideoRenderQuality $defaultQuality,
    ) {
    }

    /**
     * @param list<TranslationLanguage> $targetLanguages
     */
    public function generate(
        VideoId $videoId,
        ?VideoRenderProvider $provider = null,
        ?VideoRenderFormat $format = null,
        ?VideoRenderQuality $quality = null,
        array $targetLanguages = [],
    ): void {
        $video = $this->videoRepository->findById($videoId);

        if (null === $video) {
            return;
        }

        $lipSyncEntries = $this->lipSyncRepository->findAllDetailedByVideoId($videoId);

        if ([] === $lipSyncEntries) {
            return;
        }

        $renderProvider = $this->aiProviderResolver->resolveVideoRender($provider);
        $format ??= $this->defaultFormat;
        $quality ??= $this->defaultQuality;

        foreach ($lipSyncEntries as $entry) {
            $targetLanguage = $entry['language'];

            if ([] !== $targetLanguages && !in_array($targetLanguage, $targetLanguages, true)) {
                continue;
            }

            $lipSync = $entry['artifact'];
            $rendered = $renderProvider->render($lipSync, $format, $quality);

            $storagePath = rtrim($this->outputDirectory, '/\\')
                .DIRECTORY_SEPARATOR
                .$rendered->finalVideoId()->value
                .'.'
                .$rendered->format()->value;

            $this->finalVideoRepository->save($videoId, $targetLanguage, $rendered, $storagePath);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::FinalVideo,
                ArtifactContent::fromString(
                    $this->videoRenderJsonMapper->toJson($rendered, $targetLanguage, $storagePath),
                ),
            );
            $this->artifactRepository->save($artifact);
        }
    }
}

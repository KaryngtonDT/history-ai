<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\Handlers;

use App\Application\AudioUpload\AudioPipelineRunner;
use App\Application\AudioUpload\AudioProcessingContext;
use App\Application\AudioUpload\Messages\ProcessAudioMessage;
use App\Application\Speech\TranscriptJsonMapper;
use App\Application\Translation\DefaultTranslationLanguagesProvider;
use App\Application\Translation\VideoTranslationGenerator;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceRepositoryInterface;
use App\Domain\Source\SourceType;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use Throwable;

final class ProcessAudioHandler
{
    public function __construct(
        private readonly SourceRepositoryInterface $sourceRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly TranscriptJsonMapper $transcriptJsonMapper,
        private readonly VideoTranslationGenerator $translationGenerator,
        private readonly DefaultTranslationLanguagesProvider $defaultTranslationLanguages,
        private readonly AudioPipelineRunner $audioPipelineRunner,
    ) {
    }

    public function __invoke(ProcessAudioMessage $message): void
    {
        $audioId = new SourceId($message->audioId);
        $source = $this->sourceRepository->findById($audioId);

        if (null === $source || SourceType::Audio !== $source->type()) {
            return;
        }

        $processing = $source->startProcessing();
        $this->sourceRepository->save($processing);

        try {
            $context = new AudioProcessingContext(
                $audioId,
                (string) $processing->storagePath(),
                $processing->metadata()->displayTitle(),
            );

            $this->audioPipelineRunner->run(
                $context,
                function () use ($context): void {
                    $transcript = $this->aiProviderResolver
                        ->resolveSpeechToText()
                        ->transcribePath($context->storagePath);

                    $contentKey = new VideoId($context->audioId->value);
                    $this->transcriptRepository->save($contentKey, $transcript);

                    $artifact = Artifact::create(
                        ArtifactId::generate(),
                        new ContentId($context->audioId->value),
                        new ProcessingJobId($context->audioId->value),
                        ArtifactType::Transcript,
                        ArtifactContent::fromString($this->transcriptJsonMapper->toJson($transcript)),
                    );
                    $this->artifactRepository->save($artifact);
                },
                function () use ($context): void {
                    if ([] === $this->defaultTranslationLanguages->all()) {
                        return;
                    }

                    $this->translationGenerator->generate(
                        new VideoId($context->audioId->value),
                        $this->defaultTranslationLanguages->all(),
                    );
                },
            );

            $this->sourceRepository->save($processing->complete());
        } catch (Throwable) {
            $this->sourceRepository->save($processing->fail());
        }
    }
}

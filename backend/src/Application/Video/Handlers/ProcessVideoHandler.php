<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Application\Video\Messages\ProcessVideoMessage;
use App\Application\Speech\TranscriptJsonMapper;
use App\Application\Translation\DefaultTranslationLanguagesProvider;
use App\Application\Translation\VideoTranslationGenerator;
use App\Application\TTS\GenerateAudioConfiguration;
use App\Application\TTS\VideoAudioGenerator;
use App\Application\LipSync\GenerateLipSyncConfiguration;
use App\Application\LipSync\VideoLipSyncGenerator;
use App\Application\VoiceClone\GenerateVoiceCloneConfiguration;
use App\Application\VoiceClone\VideoVoiceCloneGenerator;
use App\Domain\AI\AIProviderResolverInterface;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use Throwable;

final class ProcessVideoHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly AIProviderResolverInterface $aiProviderResolver,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly TranscriptJsonMapper $transcriptJsonMapper,
        private readonly VideoTranslationGenerator $videoTranslationGenerator,
        private readonly DefaultTranslationLanguagesProvider $defaultTranslationLanguages,
        private readonly VideoAudioGenerator $videoAudioGenerator,
        private readonly GenerateAudioConfiguration $generateAudioConfiguration,
        private readonly VideoVoiceCloneGenerator $videoVoiceCloneGenerator,
        private readonly GenerateVoiceCloneConfiguration $generateVoiceCloneConfiguration,
        private readonly VideoLipSyncGenerator $videoLipSyncGenerator,
        private readonly GenerateLipSyncConfiguration $generateLipSyncConfiguration,
    ) {
    }

    public function __invoke(ProcessVideoMessage $message): void
    {
        $videoId = new VideoId($message->videoId);
        $job = $this->videoRepository->findById($videoId);

        if (null === $job) {
            return;
        }

        $processing = $job->startProcessing();
        $this->videoRepository->save($processing);

        try {
            $transcript = $this->aiProviderResolver
                ->resolveSpeechToText()
                ->transcribe($processing);
            $this->transcriptRepository->save($videoId, $transcript);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::Transcript,
                ArtifactContent::fromString($this->transcriptJsonMapper->toJson($transcript)),
            );
            $this->artifactRepository->save($artifact);

            if ([] !== $this->defaultTranslationLanguages->all()) {
                $this->videoTranslationGenerator->generate(
                    $videoId,
                    $this->defaultTranslationLanguages->all(),
                );
            }

            if ($this->generateAudioConfiguration->isEnabled()) {
                $this->videoAudioGenerator->generate($videoId);
            }

            if ($this->generateVoiceCloneConfiguration->isEnabled()) {
                $this->videoVoiceCloneGenerator->generate($videoId);
            }

            if ($this->generateLipSyncConfiguration->isEnabled()) {
                $this->videoLipSyncGenerator->generate($videoId);
            }

            $this->videoRepository->save($processing->complete());
        } catch (Throwable) {
            $this->videoRepository->save($processing->fail());
        }
    }
}

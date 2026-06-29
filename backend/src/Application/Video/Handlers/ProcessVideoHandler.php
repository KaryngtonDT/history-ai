<?php

declare(strict_types=1);

namespace App\Application\Video\Handlers;

use App\Application\Video\Messages\ProcessVideoMessage;
use App\Domain\Artifact\Artifact;
use App\Domain\Artifact\ArtifactContent;
use App\Domain\Artifact\ArtifactId;
use App\Domain\Artifact\ArtifactRepositoryInterface;
use App\Domain\Artifact\ArtifactType;
use App\Domain\Content\ContentId;
use App\Domain\Processing\ProcessingJobId;
use App\Domain\Speech\SpeechToTextProviderInterface;
use App\Domain\Speech\TranscriptRepositoryInterface;
use App\Domain\Video\VideoId;
use App\Domain\Video\VideoRepositoryInterface;
use App\Infrastructure\Speech\TranscriptJsonMapper;
use Throwable;

final class ProcessVideoHandler
{
    public function __construct(
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly SpeechToTextProviderInterface $speechToTextProvider,
        private readonly TranscriptRepositoryInterface $transcriptRepository,
        private readonly ArtifactRepositoryInterface $artifactRepository,
        private readonly TranscriptJsonMapper $transcriptJsonMapper,
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
            $transcript = $this->speechToTextProvider->transcribe($processing);
            $this->transcriptRepository->save($videoId, $transcript);

            $artifact = Artifact::create(
                ArtifactId::generate(),
                new ContentId($videoId->value),
                new ProcessingJobId($videoId->value),
                ArtifactType::Transcript,
                ArtifactContent::fromString($this->transcriptJsonMapper->toJson($transcript)),
            );
            $this->artifactRepository->save($artifact);

            $this->videoRepository->save($processing->complete());
        } catch (Throwable) {
            $this->videoRepository->save($processing->fail());
        }
    }
}

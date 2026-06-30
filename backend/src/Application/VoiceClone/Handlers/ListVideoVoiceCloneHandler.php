<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Handlers;

use App\Application\VoiceClone\DTO\VideoVoiceCloneSummary;
use App\Application\VoiceClone\Queries\ListVideoVoiceCloneQuery;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;

final class ListVideoVoiceCloneHandler
{
    public function __construct(
        private readonly VoiceCloneRepositoryInterface $voiceCloneRepository,
    ) {
    }

    /**
     * @return list<VideoVoiceCloneSummary>
     */
    public function __invoke(ListVideoVoiceCloneQuery $query): array
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVoiceCloneException('Video id must be a valid UUID.');
        }

        $artifacts = $this->voiceCloneRepository->findAllByVideoId($videoId);
        $summaries = [];

        foreach ($artifacts as $artifact) {
            $summaries[] = new VideoVoiceCloneSummary(
                videoId: $videoId->value,
                artifactId: $artifact->artifactId()->value,
                sourceAudioId: $artifact->sourceAudioId()->value,
                clonedAudioId: $artifact->clonedAudioId()->value,
                targetLanguage: $artifact->targetLanguage()->value,
                provider: $artifact->provider()->value,
                duration: $artifact->profile()->duration(),
                sampleRate: $artifact->profile()->sampleRate(),
            );
        }

        return $summaries;
    }
}

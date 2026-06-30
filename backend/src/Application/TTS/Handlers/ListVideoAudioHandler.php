<?php

declare(strict_types=1);

namespace App\Application\TTS\Handlers;

use App\Application\TTS\DTO\VideoAudioSummary;
use App\Application\TTS\Queries\ListVideoAudioQuery;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class ListVideoAudioHandler
{
    public function __construct(
        private readonly AudioRepositoryInterface $audioRepository,
    ) {
    }

    /**
     * @return list<VideoAudioSummary>
     */
    public function __invoke(ListVideoAudioQuery $query): array
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidAudioArtifactException('Video id must be a valid UUID.');
        }

        $audioArtifacts = $this->audioRepository->findAllByVideoId($videoId);
        $summaries = [];

        foreach ($audioArtifacts as $audio) {
            $summaries[] = new VideoAudioSummary(
                videoId: $videoId->value,
                audioId: $audio->audioId()->value,
                translationId: $audio->translationId()->value,
                targetLanguage: $audio->targetLanguage()->value,
                provider: $audio->provider()->value,
                voiceId: $audio->voice()->voiceId(),
                voiceDisplayName: $audio->voice()->displayName(),
                duration: $audio->duration(),
                format: $audio->format()->value,
            );
        }

        return $summaries;
    }
}

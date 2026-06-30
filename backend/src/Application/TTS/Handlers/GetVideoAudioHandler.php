<?php

declare(strict_types=1);

namespace App\Application\TTS\Handlers;

use App\Application\TTS\DTO\VideoAudioResult;
use App\Application\TTS\Queries\GetVideoAudioQuery;
use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class GetVideoAudioHandler
{
    public function __construct(
        private readonly AudioRepositoryInterface $audioRepository,
    ) {
    }

    public function __invoke(GetVideoAudioQuery $query): VideoAudioResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidAudioArtifactException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($query->language);

        if ([] === $parsed) {
            throw new InvalidAudioArtifactException(sprintf('Invalid target language "%s".', $query->language));
        }

        $audio = $this->audioRepository->findByVideoIdAndLanguage($videoId, $parsed[0]);

        if (null === $audio) {
            throw new InvalidAudioArtifactException('Audio not found for the requested language.');
        }

        return new VideoAudioResult(
            videoId: $videoId->value,
            audioId: $audio->audioId()->value,
            translationId: $audio->translationId()->value,
            targetLanguage: $audio->targetLanguage()->value,
            provider: $audio->provider()->value,
            voiceId: $audio->voice()->voiceId(),
            voiceDisplayName: $audio->voice()->displayName(),
            voiceLanguage: $audio->voice()->language()->value,
            voiceGender: $audio->voice()->gender()->value,
            duration: $audio->duration(),
            format: $audio->format()->value,
            downloadUrl: sprintf('/api/videos/%s/audio/%s/stream', $videoId->value, $audio->targetLanguage()->value),
        );
    }
}

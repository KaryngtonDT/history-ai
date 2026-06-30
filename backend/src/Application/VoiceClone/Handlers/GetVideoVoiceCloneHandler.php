<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Application\VoiceClone\DTO\VideoVoiceCloneResult;
use App\Application\VoiceClone\Queries\GetVideoVoiceCloneQuery;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;

final class GetVideoVoiceCloneHandler
{
    public function __construct(
        private readonly VoiceCloneRepositoryInterface $voiceCloneRepository,
        private readonly AudioRepositoryInterface $audioRepository,
    ) {
    }

    public function __invoke(GetVideoVoiceCloneQuery $query): VideoVoiceCloneResult
    {
        try {
            $videoId = new VideoId($query->videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVoiceCloneException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($query->language);

        if ([] === $parsed) {
            throw new InvalidVoiceCloneException(sprintf('Invalid target language "%s".', $query->language));
        }

        $artifact = $this->voiceCloneRepository->findByVideoIdAndLanguage($videoId, $parsed[0]);

        if (null === $artifact) {
            throw new InvalidVoiceCloneException('Voice clone not found for the requested language.');
        }

        $sourceAudio = $this->audioRepository->findByVideoIdAndLanguage($videoId, $parsed[0]);
        $originalStreamPath = $sourceAudio?->storagePath() ?? '';

        return new VideoVoiceCloneResult(
            videoId: $videoId->value,
            artifactId: $artifact->artifactId()->value,
            sourceAudioId: $artifact->sourceAudioId()->value,
            clonedAudioId: $artifact->clonedAudioId()->value,
            targetLanguage: $artifact->targetLanguage()->value,
            provider: $artifact->provider()->value,
            sourceLanguage: $artifact->profile()->language()->value,
            duration: $artifact->profile()->duration(),
            sampleRate: $artifact->profile()->sampleRate(),
            originalStreamPath: $originalStreamPath,
        );
    }
}

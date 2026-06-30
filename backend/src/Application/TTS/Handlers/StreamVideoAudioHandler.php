<?php

declare(strict_types=1);

namespace App\Application\TTS\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\TTS\AudioRepositoryInterface;
use App\Domain\TTS\Exception\InvalidAudioArtifactException;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class StreamVideoAudioHandler
{
    public function __construct(
        private readonly AudioRepositoryInterface $audioRepository,
    ) {
    }

    public function __invoke(string $videoId, string $language): string
    {
        try {
            $id = new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidAudioArtifactException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($language);

        if ([] === $parsed) {
            throw new InvalidAudioArtifactException(sprintf('Invalid target language "%s".', $language));
        }

        $audio = $this->audioRepository->findByVideoIdAndLanguage($id, $parsed[0]);

        if (null === $audio) {
            throw new InvalidAudioArtifactException('Audio not found for the requested language.');
        }

        $path = $audio->storagePath();

        if (!is_file($path)) {
            throw new InvalidAudioArtifactException('Audio file is not available on disk.');
        }

        return $path;
    }
}

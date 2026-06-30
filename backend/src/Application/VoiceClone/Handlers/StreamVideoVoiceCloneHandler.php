<?php

declare(strict_types=1);

namespace App\Application\VoiceClone\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VoiceClone\Exception\InvalidVoiceCloneException;
use App\Domain\VoiceClone\VoiceCloneRepositoryInterface;

final class StreamVideoVoiceCloneHandler
{
    public function __construct(
        private readonly VoiceCloneRepositoryInterface $voiceCloneRepository,
    ) {
    }

    public function __invoke(string $videoId, string $language): string
    {
        try {
            $id = new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVoiceCloneException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($language);

        if ([] === $parsed) {
            throw new InvalidVoiceCloneException(sprintf('Invalid target language "%s".', $language));
        }

        $artifact = $this->voiceCloneRepository->findByVideoIdAndLanguage($id, $parsed[0]);

        if (null === $artifact) {
            throw new InvalidVoiceCloneException('Voice clone not found for the requested language.');
        }

        $path = $artifact->storagePath();

        if (!is_file($path)) {
            throw new InvalidVoiceCloneException('Cloned audio file is not available on disk.');
        }

        return $path;
    }
}

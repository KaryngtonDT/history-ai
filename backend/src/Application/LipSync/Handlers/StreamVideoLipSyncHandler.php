<?php

declare(strict_types=1);

namespace App\Application\LipSync\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\LipSync\Exception\InvalidLipSyncException;
use App\Domain\LipSync\LipSyncRepositoryInterface;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;

final class StreamVideoLipSyncHandler
{
    public function __construct(
        private readonly LipSyncRepositoryInterface $lipSyncRepository,
    ) {
    }

    public function __invoke(string $videoId, string $language): string
    {
        try {
            $id = new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidLipSyncException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($language);

        if ([] === $parsed) {
            throw new InvalidLipSyncException(sprintf('Invalid target language "%s".', $language));
        }

        $artifact = $this->lipSyncRepository->findByVideoIdAndLanguage($id, $parsed[0]);

        if (null === $artifact) {
            throw new InvalidLipSyncException('Lip sync not found for the requested language.');
        }

        $path = $artifact->video()->storagePath();

        if (!is_file($path)) {
            throw new InvalidLipSyncException('Synchronized video file is not available on disk.');
        }

        return $path;
    }
}

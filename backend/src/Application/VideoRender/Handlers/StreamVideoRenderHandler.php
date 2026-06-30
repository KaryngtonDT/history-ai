<?php

declare(strict_types=1);

namespace App\Application\VideoRender\Handlers;

use App\Application\Translation\TranslationLanguageListParser;
use App\Domain\Video\Exception\InvalidVideoIdException;
use App\Domain\Video\VideoId;
use App\Domain\VideoRender\Exception\InvalidVideoRenderException;
use App\Domain\VideoRender\FinalVideoRepositoryInterface;

final class StreamVideoRenderHandler
{
    public function __construct(
        private readonly FinalVideoRepositoryInterface $finalVideoRepository,
    ) {
    }

    public function __invoke(string $videoId, string $language): string
    {
        try {
            $id = new VideoId($videoId);
        } catch (InvalidVideoIdException) {
            throw new InvalidVideoRenderException('Video id must be a valid UUID.');
        }

        $parsed = TranslationLanguageListParser::parse($language);

        if ([] === $parsed) {
            throw new InvalidVideoRenderException(sprintf('Invalid target language "%s".', $language));
        }

        $path = $this->finalVideoRepository->findStoragePathByVideoIdAndLanguage($id, $parsed[0]);

        if (null === $path) {
            throw new InvalidVideoRenderException('Final render not found for the requested language.');
        }

        if (!is_file($path)) {
            throw new InvalidVideoRenderException('Final video file is not available on disk.');
        }

        return $path;
    }
}

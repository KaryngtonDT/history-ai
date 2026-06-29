<?php

declare(strict_types=1);

namespace App\Domain\Translation;

use App\Domain\Video\VideoId;

interface TranslationRepositoryInterface
{
    public function save(VideoId $videoId, Translation $translation): void;

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?Translation;

    /**
     * @return list<Translation>
     */
    public function findAllByVideoId(VideoId $videoId): array;
}

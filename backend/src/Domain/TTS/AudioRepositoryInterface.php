<?php

declare(strict_types=1);

namespace App\Domain\TTS;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;

interface AudioRepositoryInterface
{
    public function save(VideoId $videoId, AudioArtifact $audio): void;

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?AudioArtifact;

    /**
     * @return list<AudioArtifact>
     */
    public function findAllByVideoId(VideoId $videoId): array;
}

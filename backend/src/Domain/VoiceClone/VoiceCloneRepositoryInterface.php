<?php

declare(strict_types=1);

namespace App\Domain\VoiceClone;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;

interface VoiceCloneRepositoryInterface
{
    public function save(VideoId $videoId, VoiceCloneArtifact $artifact): void;

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?VoiceCloneArtifact;

    /**
     * @return list<VoiceCloneArtifact>
     */
    public function findAllByVideoId(VideoId $videoId): array;
}

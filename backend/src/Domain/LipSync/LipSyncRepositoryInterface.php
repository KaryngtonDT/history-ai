<?php

declare(strict_types=1);

namespace App\Domain\LipSync;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;

interface LipSyncRepositoryInterface
{
    public function save(VideoId $videoId, TranslationLanguage $targetLanguage, LipSyncArtifact $artifact): void;

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?LipSyncArtifact;

    /**
     * @return list<LipSyncArtifact>
     */
    public function findAllByVideoId(VideoId $videoId): array;

    /**
     * @return list<array{language: TranslationLanguage, artifact: LipSyncArtifact}>
     */
    public function findAllDetailedByVideoId(VideoId $videoId): array;
}

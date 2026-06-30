<?php

declare(strict_types=1);

namespace App\Domain\VideoRender;

use App\Domain\Translation\TranslationLanguage;
use App\Domain\Video\VideoId;

interface FinalVideoRepositoryInterface
{
    public function save(VideoId $videoId, TranslationLanguage $targetLanguage, FinalVideoArtifact $artifact, string $storagePath): void;

    public function findByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?FinalVideoArtifact;

    public function findStoragePathByVideoIdAndLanguage(VideoId $videoId, TranslationLanguage $targetLanguage): ?string;

    /**
     * @return list<array{language: TranslationLanguage, artifact: FinalVideoArtifact, storagePath: string}>
     */
    public function findAllDetailedByVideoId(VideoId $videoId): array;
}

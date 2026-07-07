<?php

declare(strict_types=1);

namespace App\Domain\Speech;

use App\Domain\Video\VideoId;

interface TranscriptRepositoryInterface
{
    public function save(VideoId $videoId, Transcript $transcript, ?TranscriptMetadata $metadata = null): void;

    public function findByVideoId(VideoId $videoId): ?Transcript;

    public function findMetadataByVideoId(VideoId $videoId): ?TranscriptMetadata;
}

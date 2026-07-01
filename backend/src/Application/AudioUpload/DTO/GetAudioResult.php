<?php

declare(strict_types=1);

namespace App\Application\AudioUpload\DTO;

use App\Domain\Source\Source;
use App\Domain\Source\SourceId;
use App\Domain\Source\SourceStatus;
use App\Domain\Source\SourceType;

final readonly class GetAudioResult
{
    public function __construct(
        public SourceId $audioId,
        public string $title,
        public string $originalFilename,
        public SourceStatus $status,
        public SourceType $type,
        public string $createdAt,
    ) {
    }

    public static function fromSource(Source $source): self
    {
        return new self(
            audioId: $source->id(),
            title: $source->metadata()->displayTitle(),
            originalFilename: $source->metadata()->originalFilename,
            status: $source->status(),
            type: $source->type(),
            createdAt: $source->createdAt()->format(DATE_ATOM),
        );
    }
}

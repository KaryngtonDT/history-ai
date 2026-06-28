<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatSource;

final readonly class ChatSourceResult
{
    public function __construct(
        public string $artifactId,
        public string $chunkId,
        public string $text,
        public float $score,
    ) {
    }

    public static function fromDomain(ChatSource $source): self
    {
        return new self(
            artifactId: $source->artifactId()->value,
            chunkId: $source->chunkId()->value,
            text: $source->text(),
            score: $source->score()->value(),
        );
    }
}

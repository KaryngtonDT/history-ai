<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatCitation;

final readonly class ChatCitationResult
{
    public function __construct(
        public int $number,
        public string $artifactId,
        public string $chunkId,
        public float $score,
    ) {
    }

    public static function fromDomain(ChatCitation $citation): self
    {
        $source = $citation->source();

        return new self(
            number: $citation->number(),
            artifactId: $source->artifactId()->value,
            chunkId: $source->chunkId()->value,
            score: $source->score()->value(),
        );
    }
}

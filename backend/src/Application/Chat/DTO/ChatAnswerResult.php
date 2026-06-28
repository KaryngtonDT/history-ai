<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSource;

final readonly class ChatAnswerResult
{
    /**
     * @param list<ChatSourceResult> $sources
     */
    public function __construct(
        public string $answer,
        public array $sources,
    ) {
    }

    public static function fromDomain(ChatResponse $response): self
    {
        return new self(
            answer: $response->answer(),
            sources: array_map(
                static fn (ChatSource $source): ChatSourceResult => ChatSourceResult::fromDomain($source),
                $response->sources()->sources(),
            ),
        );
    }
}

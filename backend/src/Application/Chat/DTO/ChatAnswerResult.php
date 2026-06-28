<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatCitation;
use App\Domain\Chat\ChatResponse;
use App\Domain\Chat\ChatSource;

final readonly class ChatAnswerResult
{
    /**
     * @param list<ChatSourceResult> $sources
     * @param list<ChatCitationResult> $citations
     */
    public function __construct(
        public string $answer,
        public array $sources,
        public array $citations,
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
            citations: array_map(
                static fn (ChatCitation $citation): ChatCitationResult => ChatCitationResult::fromDomain($citation),
                $response->citations()->citations(),
            ),
        );
    }
}

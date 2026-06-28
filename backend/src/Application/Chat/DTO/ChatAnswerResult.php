<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatAnswer;
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

    public static function fromDomain(ChatAnswer $answer): self
    {
        return new self(
            answer: $answer->answer(),
            sources: array_map(
                static fn (ChatSource $source): ChatSourceResult => ChatSourceResult::fromDomain($source),
                $answer->sources()->sources(),
            ),
        );
    }
}

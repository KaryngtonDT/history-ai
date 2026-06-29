<?php

declare(strict_types=1);

namespace App\Application\Chat\DTO;

use App\Domain\Chat\ChatMessage;
use App\Domain\Chat\ChatMessageRole;

final readonly class ConversationMessageResult
{
    public function __construct(
        public string $role,
        public string $text,
    ) {
    }

    public static function fromDomain(ChatMessage $message): self
    {
        return new self(
            role: $message->role()->value,
            text: $message->content(),
        );
    }

    public static function user(string $text): self
    {
        return new self(ChatMessageRole::User->value, $text);
    }

    public static function assistant(string $text): self
    {
        return new self(ChatMessageRole::Assistant->value, $text);
    }
}

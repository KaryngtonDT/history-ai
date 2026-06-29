<?php

declare(strict_types=1);

namespace App\Domain\Chat;

final readonly class ConversationCollection
{
    /** @var list<Conversation> */
    private array $conversations;

    /**
     * @param list<Conversation> $conversations
     */
    public function __construct(array $conversations)
    {
        $this->conversations = array_values($conversations);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<Conversation>
     */
    public function conversations(): array
    {
        return $this->conversations;
    }

    public function count(): int
    {
        return count($this->conversations);
    }

    public function isEmpty(): bool
    {
        return [] === $this->conversations;
    }
}

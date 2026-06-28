<?php

declare(strict_types=1);

namespace App\Domain\Chat;

final readonly class ChatConversation
{
    /** @var list<ChatMessage> */
    private array $messages;

    /**
     * @param list<ChatMessage> $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = array_values($messages);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ChatMessage>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    public function isEmpty(): bool
    {
        return [] === $this->messages;
    }

    public function withMessage(ChatMessage $message): self
    {
        return new self([...$this->messages, $message]);
    }
}

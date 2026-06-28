<?php

declare(strict_types=1);

namespace App\Domain\Chat;

use App\Domain\Chat\Exception\InvalidChatCitationException;

final readonly class ChatCitationCollection
{
    /** @var list<ChatCitation> */
    private array $citations;

    /**
     * @param list<ChatCitation> $citations
     */
    public function __construct(array $citations)
    {
        $this->citations = array_values($citations);
        $this->assertSequentialNumbering();
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ChatCitation>
     */
    public function citations(): array
    {
        return $this->citations;
    }

    public function count(): int
    {
        return count($this->citations);
    }

    public function isEmpty(): bool
    {
        return [] === $this->citations;
    }

    private function assertSequentialNumbering(): void
    {
        $expectedNumber = 1;

        foreach ($this->citations as $citation) {
            if ($citation->number() !== $expectedNumber) {
                throw new InvalidChatCitationException(
                    sprintf(
                        'Chat citations must be numbered sequentially from 1, expected %d, got %d.',
                        $expectedNumber,
                        $citation->number(),
                    ),
                );
            }

            ++$expectedNumber;
        }
    }
}

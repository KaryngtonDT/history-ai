<?php

declare(strict_types=1);

namespace App\Domain\Agent;

final readonly class ConversationMemoryResult
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $summary,
        private int $messageCount,
        private array $metadata,
    ) {
    }

    public static function empty(): self
    {
        return new self(
            summary: 'No conversation memory.',
            messageCount: 0,
            metadata: [],
        );
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function messageCount(): int
    {
        return $this->messageCount;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function equals(self $other): bool
    {
        return $this->summary === $other->summary
            && $this->messageCount === $other->messageCount
            && $this->metadata === $other->metadata;
    }
}

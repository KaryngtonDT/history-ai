<?php

declare(strict_types=1);

namespace App\Domain\Agent;

final readonly class AgentMetadata
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private array $values)
    {
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return $this->values;
    }

    public function equals(self $other): bool
    {
        return $this->values === $other->values;
    }
}

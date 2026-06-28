<?php

declare(strict_types=1);

namespace App\Domain\Chat;

final readonly class ChatSourceCollection
{
    /** @var list<ChatSource> */
    private array $sources;

    /**
     * @param list<ChatSource> $sources
     */
    public function __construct(array $sources)
    {
        $this->sources = array_values($sources);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return list<ChatSource>
     */
    public function sources(): array
    {
        return $this->sources;
    }

    public function count(): int
    {
        return count($this->sources);
    }

    public function isEmpty(): bool
    {
        return [] === $this->sources;
    }
}

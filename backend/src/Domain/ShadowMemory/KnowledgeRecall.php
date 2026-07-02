<?php

declare(strict_types=1);

namespace App\Domain\ShadowMemory;

final readonly class KnowledgeRecall
{
    /**
     * @param list<string> $promptLines
     */
    public function __construct(
        private string $summary,
        private array $promptLines,
        private ?KnowledgeItem $primaryItem,
        private ?KnowledgeItem $prerequisiteItem,
    ) {
    }

    public static function empty(): self
    {
        return new self('No prior knowledge recalled.', [], null, null);
    }

    public function summary(): string
    {
        return $this->summary;
    }

    /** @return list<string> */
    public function promptLines(): array
    {
        return $this->promptLines;
    }

    public function primaryItem(): ?KnowledgeItem
    {
        return $this->primaryItem;
    }

    public function prerequisiteItem(): ?KnowledgeItem
    {
        return $this->prerequisiteItem;
    }
}

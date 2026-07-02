<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

final readonly class KnowledgeNode
{
    /** @param list<string> $sources */
    public function __construct(
        private string $key,
        private string $label,
        private KnowledgeNodeType $type,
        private string $explanation,
        private array $sources,
    ) {
    }

    public static function create(
        string $key,
        string $label,
        KnowledgeNodeType $type = KnowledgeNodeType::Concept,
        string $explanation = '',
        array $sources = [],
    ): self {
        return new self($key, $label, $type, $explanation, $sources);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function type(): KnowledgeNodeType
    {
        return $this->type;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    /** @return list<string> */
    public function sources(): array
    {
        return $this->sources;
    }

    public function withSource(string $source): self
    {
        if (in_array($source, $this->sources, true)) {
            return $this;
        }

        return new self($this->key, $this->label, $this->type, $this->explanation, [...$this->sources, $source]);
    }
}

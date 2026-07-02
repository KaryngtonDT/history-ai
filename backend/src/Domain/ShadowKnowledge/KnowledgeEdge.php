<?php

declare(strict_types=1);

namespace App\Domain\ShadowKnowledge;

final readonly class KnowledgeEdge
{
    public function __construct(
        private string $id,
        private string $fromKey,
        private string $toKey,
        private KnowledgeEdgeType $type,
        private string $label,
        private string $reason,
        private string $source,
        private KnowledgeConfidence $confidence,
    ) {
    }

    public static function link(
        string $fromKey,
        string $toKey,
        KnowledgeEdgeType $type,
        string $label,
        string $reason,
        string $source = 'preset',
        KnowledgeConfidence $confidence = KnowledgeConfidence::High,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $fromKey,
            $toKey,
            $type,
            $label,
            $reason,
            $source,
            $confidence,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fromKey(): string
    {
        return $this->fromKey;
    }

    public function toKey(): string
    {
        return $this->toKey;
    }

    public function type(): KnowledgeEdgeType
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function confidence(): KnowledgeConfidence
    {
        return $this->confidence;
    }
}

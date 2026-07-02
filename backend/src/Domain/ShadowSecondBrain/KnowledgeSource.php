<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeSource
{
    public function __construct(
        private string $id,
        private KnowledgeSourceType $type,
        private string $label,
        private string $resourceId,
        private string $resourceLabel,
        private ?string $conceptKey = null,
        private ?\DateTimeImmutable $occurredAt = null,
        private ?string $detail = null,
        private ?string $linkHint = null,
    ) {
        if ('' === trim($id)) {
            throw new InvalidShadowSecondBrainException('Knowledge source id cannot be empty.');
        }

        if ('' === trim($label)) {
            throw new InvalidShadowSecondBrainException('Knowledge source label cannot be empty.');
        }

        if ('' === trim($resourceId)) {
            throw new InvalidShadowSecondBrainException('Knowledge source resource id cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): KnowledgeSourceType
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function resourceId(): string
    {
        return $this->resourceId;
    }

    public function resourceLabel(): string
    {
        return $this->resourceLabel;
    }

    public function conceptKey(): ?string
    {
        return $this->conceptKey;
    }

    public function occurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function detail(): ?string
    {
        return $this->detail;
    }

    public function linkHint(): ?string
    {
        return $this->linkHint;
    }
}

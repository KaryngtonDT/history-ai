<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeTimelineEvent
{
    public function __construct(
        private string $id,
        private string $label,
        private \DateTimeImmutable $occurredAt,
        private ?string $conceptKey = null,
        private ?KnowledgeSourceType $sourceType = null,
        private ?string $resourceId = null,
    ) {
        if ('' === trim($id)) {
            throw new InvalidShadowSecondBrainException('Knowledge timeline event id cannot be empty.');
        }

        if ('' === trim($label)) {
            throw new InvalidShadowSecondBrainException('Knowledge timeline event label cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function conceptKey(): ?string
    {
        return $this->conceptKey;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function sourceType(): ?KnowledgeSourceType
    {
        return $this->sourceType;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }
}

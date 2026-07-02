<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeBookmark
{
    /** @param list<string> $tags */
    public function __construct(
        private string $id,
        private string $label,
        private array $tags,
        private ?string $conceptKey = null,
        private ?KnowledgeSourceType $resourceType = null,
        private ?string $resourceId = null,
    ) {
        if ('' === trim($id)) {
            throw new InvalidShadowSecondBrainException('Knowledge bookmark id cannot be empty.');
        }

        if ('' === trim($label)) {
            throw new InvalidShadowSecondBrainException('Knowledge bookmark label cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function conceptKey(): ?string
    {
        return $this->conceptKey;
    }

    public function resourceType(): ?KnowledgeSourceType
    {
        return $this->resourceType;
    }

    public function resourceId(): ?string
    {
        return $this->resourceId;
    }

    public function label(): string
    {
        return $this->label;
    }

    /** @return list<string> */
    public function tags(): array
    {
        return $this->tags;
    }
}

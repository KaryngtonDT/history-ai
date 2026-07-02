<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeReference
{
    public function __construct(
        private string $sourceId,
        private string $conceptKey,
        private ?string $excerpt = null,
    ) {
        if ('' === trim($sourceId)) {
            throw new InvalidShadowSecondBrainException('Knowledge reference source id cannot be empty.');
        }

        if ('' === trim($conceptKey)) {
            throw new InvalidShadowSecondBrainException('Knowledge reference concept key cannot be empty.');
        }
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function conceptKey(): string
    {
        return $this->conceptKey;
    }

    public function excerpt(): ?string
    {
        return $this->excerpt;
    }
}

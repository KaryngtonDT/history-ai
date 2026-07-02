<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeNote
{
    public function __construct(
        private string $id,
        private string $body,
        private \DateTimeImmutable $createdAt,
        private ?string $conceptKey = null,
    ) {
        if ('' === trim($id)) {
            throw new InvalidShadowSecondBrainException('Knowledge note id cannot be empty.');
        }

        if ('' === trim($body)) {
            throw new InvalidShadowSecondBrainException('Knowledge note body cannot be empty.');
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

    public function body(): string
    {
        return $this->body;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

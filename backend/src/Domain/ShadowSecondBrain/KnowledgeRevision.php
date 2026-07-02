<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeRevision
{
    public function __construct(
        private string $conceptKey,
        private \DateTimeImmutable $dueAt,
        private string $reason,
    ) {
        if ('' === trim($conceptKey)) {
            throw new InvalidShadowSecondBrainException('Knowledge revision concept key cannot be empty.');
        }
    }

    public function conceptKey(): string
    {
        return $this->conceptKey;
    }

    public function dueAt(): \DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeInsight
{
    public function __construct(
        private string $id,
        private string $kind,
        private string $label,
        private string $detail,
        private ?string $conceptKey = null,
    ) {
        if ('' === trim($id)) {
            throw new InvalidShadowSecondBrainException('Knowledge insight id cannot be empty.');
        }

        if ('' === trim($kind)) {
            throw new InvalidShadowSecondBrainException('Knowledge insight kind cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->id;
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function conceptKey(): ?string
    {
        return $this->conceptKey;
    }
}

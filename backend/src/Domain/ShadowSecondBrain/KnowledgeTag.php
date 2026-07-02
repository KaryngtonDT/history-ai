<?php

declare(strict_types=1);

namespace App\Domain\ShadowSecondBrain;

use App\Domain\ShadowSecondBrain\Exception\InvalidShadowSecondBrainException;

final readonly class KnowledgeTag
{
    public function __construct(
        private string $key,
        private string $label,
    ) {
        if ('' === trim($key)) {
            throw new InvalidShadowSecondBrainException('Knowledge tag key cannot be empty.');
        }
    }

    public static function create(string $key, string $label): self
    {
        return new self(trim($key), trim($label));
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }
}

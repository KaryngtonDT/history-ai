<?php

declare(strict_types=1);

namespace App\Domain\Artifact;

use App\Domain\Artifact\Exception\InvalidArtifactException;

final readonly class ArtifactContent
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if ('' === trim($value)) {
            throw new InvalidArtifactException('Artifact content cannot be empty.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}

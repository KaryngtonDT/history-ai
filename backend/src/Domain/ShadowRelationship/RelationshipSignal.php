<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;

final readonly class RelationshipSignal
{
    public function __construct(
        private string $id,
        private string $source,
        private string $kind,
        /** @var array<string, mixed> */
        private array $payload,
        private \DateTimeImmutable $recordedAt,
    ) {
        if ('' === trim($source) || '' === trim($kind)) {
            throw new InvalidRelationshipProfileException('Relationship signal source and kind cannot be empty.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(string $source, string $kind, array $payload): self
    {
        return new self(
            bin2hex(random_bytes(8)),
            $source,
            $kind,
            $payload,
            new \DateTimeImmutable(),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function kind(): string
    {
        return $this->kind;
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payload;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }
}

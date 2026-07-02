<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;

final readonly class SharedReference
{
    public function __construct(
        private string $id,
        private SharedReferenceKind $kind,
        private string $label,
        private string $detail,
        private ?string $sessionId,
        private ?string $videoId,
        private \DateTimeImmutable $recordedAt,
    ) {
        if ('' === trim($label)) {
            throw new InvalidRelationshipProfileException('Shared reference label cannot be empty.');
        }
    }

    public static function create(
        SharedReferenceKind $kind,
        string $label,
        string $detail = '',
        ?string $sessionId = null,
        ?string $videoId = null,
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $kind,
            $label,
            $detail,
            $sessionId,
            $videoId,
            new \DateTimeImmutable(),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function kind(): SharedReferenceKind
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

    public function sessionId(): ?string
    {
        return $this->sessionId;
    }

    public function videoId(): ?string
    {
        return $this->videoId;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }
}

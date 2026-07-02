<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;

final readonly class RelationshipPendingChange
{
    public function __construct(
        private string $id,
        private string $label,
        private RelationshipTrait $proposedTrait,
        private string $status,
        private \DateTimeImmutable $createdAt,
    ) {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            throw new InvalidRelationshipProfileException('Invalid pending change status.');
        }
    }

    public static function propose(RelationshipTrait $trait, string $label): self
    {
        return new self(
            bin2hex(random_bytes(8)),
            $label,
            $trait,
            'pending',
            new \DateTimeImmutable(),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function proposedTrait(): RelationshipTrait
    {
        return $this->proposedTrait;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function approve(): self
    {
        return new self($this->id, $this->label, $this->proposedTrait->confirm(), 'approved', $this->createdAt);
    }

    public function reject(): self
    {
        return new self($this->id, $this->label, $this->proposedTrait, 'rejected', $this->createdAt);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;

final readonly class RelationshipObservation
{
    public function __construct(
        private string $id,
        private RelationshipObservationType $type,
        private string $label,
        private string $detail,
        private \DateTimeImmutable $recordedAt,
    ) {
        if ('' === trim($label)) {
            throw new InvalidRelationshipProfileException('Relationship observation label cannot be empty.');
        }
    }

    public static function record(
        RelationshipObservationType $type,
        string $label,
        string $detail = '',
    ): self {
        return new self(
            bin2hex(random_bytes(8)),
            $type,
            $label,
            $detail,
            new \DateTimeImmutable(),
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function type(): RelationshipObservationType
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function recordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;

final readonly class RelationshipTrait
{
    public function __construct(
        private RelationshipTraitType $type,
        private string $key,
        private string $label,
        private RelationshipStrength $strength,
        private string $source,
        private bool $confirmed,
        private bool $enabled,
        private string $explanation,
    ) {
        if ('' === trim($key) || '' === trim($label)) {
            throw new InvalidRelationshipProfileException('Relationship trait key and label cannot be empty.');
        }
    }

    public static function explicit(
        RelationshipTraitType $type,
        string $key,
        string $label,
        RelationshipStrength $strength,
        string $explanation,
    ): self {
        return new self($type, $key, $label, $strength, 'user', true, true, $explanation);
    }

    public static function inferred(
        RelationshipTraitType $type,
        string $key,
        string $label,
        RelationshipStrength $strength,
        string $explanation,
    ): self {
        return new self($type, $key, $label, $strength, 'signal', false, true, $explanation);
    }

    public function type(): RelationshipTraitType
    {
        return $this->type;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function strength(): RelationshipStrength
    {
        return $this->strength;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function confirmed(): bool
    {
        return $this->confirmed;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function explanation(): string
    {
        return $this->explanation;
    }

    public function withStrength(RelationshipStrength $strength): self
    {
        return new self($this->type, $this->key, $this->label, $strength, $this->source, $this->confirmed, $this->enabled, $this->explanation);
    }

    public function confirm(string $source = 'user'): self
    {
        return new self($this->type, $this->key, $this->label, $this->strength, $source, true, true, $this->explanation);
    }

    public function disable(): self
    {
        return new self($this->type, $this->key, $this->label, $this->strength, $this->source, $this->confirmed, false, $this->explanation);
    }
}

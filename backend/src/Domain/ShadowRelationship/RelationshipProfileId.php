<?php

declare(strict_types=1);

namespace App\Domain\ShadowRelationship;

use App\Domain\ShadowRelationship\Exception\InvalidRelationshipProfileException;
use Symfony\Component\Uid\Uuid;

final readonly class RelationshipProfileId
{
    public function __construct(public string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidRelationshipProfileException('Relationship profile id must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::v4()->toRfc4122());
    }
}

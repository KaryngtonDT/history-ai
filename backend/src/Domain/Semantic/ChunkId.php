<?php

declare(strict_types=1);

namespace App\Domain\Semantic;

use App\Domain\Artifact\ArtifactId;
use App\Domain\Semantic\Exception\InvalidChunkException;

final readonly class ChunkId
{
    public function __construct(public string $value)
    {
        if (!self::isValid($value)) {
            throw new InvalidChunkException('Chunk id must be a valid UUID.');
        }
    }

    public static function derive(ArtifactId $artifactId, ChunkPosition $position): self
    {
        $hash = hash('sha256', sprintf('%s#chunk#%d', $artifactId->value, $position->value()));
        $hex = substr($hash, 0, 32);

        return new self(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function isValid(string $value): bool
    {
        return 1 === preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value,
        );
    }
}

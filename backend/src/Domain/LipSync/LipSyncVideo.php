<?php

declare(strict_types=1);

namespace App\Domain\LipSync;

use App\Domain\LipSync\Exception\InvalidLipSyncException;

final readonly class LipSyncVideo
{
    public function __construct(
        private LipSyncVideoId $synchronizedVideoId,
        private string $storagePath,
        private float $duration,
    ) {
        if ('' === trim($this->storagePath)) {
            throw new InvalidLipSyncException('Lip sync video storage path cannot be empty.');
        }

        if ($this->duration < 0) {
            throw new InvalidLipSyncException('Lip sync video duration cannot be negative.');
        }
    }

    public static function create(
        LipSyncVideoId $synchronizedVideoId,
        string $storagePath,
        float $duration,
    ): self {
        return new self($synchronizedVideoId, $storagePath, $duration);
    }

    public function synchronizedVideoId(): LipSyncVideoId
    {
        return $this->synchronizedVideoId;
    }

    public function storagePath(): string
    {
        return $this->storagePath;
    }

    public function duration(): float
    {
        return $this->duration;
    }
}

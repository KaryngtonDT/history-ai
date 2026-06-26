<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Processing;

use App\Presentation\Http\Request\Processing\Exception\InvalidProcessingRequestException;

final readonly class UpdateProcessingProgressRequest
{
    public function __construct(
        public int $progress,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (!isset($payload['progress']) || !is_int($payload['progress'])) {
            if (isset($payload['progress']) && is_numeric($payload['progress'])) {
                return new self((int) $payload['progress']);
            }

            throw new InvalidProcessingRequestException('Progress is required.');
        }

        return new self($payload['progress']);
    }
}
